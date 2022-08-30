<?php

namespace HumbleCore\PostTypes;

use HumbleCore\Support\Facades\Action;
use Illuminate\Support\Facades\Blade;

class PostSorter
{
    public $postType;

    public function __construct(PostType $postType)
    {
        $this->postType = $postType;

        Action::add('admin_menu', function () {
            $this->registerPage();
        });

        Action::add('wp_ajax_updatePostsOrder', function () {
            $this->registerAjaxHandler();
        });
    }

    protected function registerPage()
    {
        $parentSlug = $this->postType->name === 'post' ? 'edit.php' : 'edit.php?post_type='.$this->postType->name;

        $label = __('Sort', 'humble_core').' '.$this->postType->labels['name'];

        add_submenu_page(
            $parentSlug,
            $label,
            $label,
            'edit_others_posts',
            "{$this->postType->name}-sort",
            function () {
                $this->renderPage();
            }
        );
    }

    protected function registerAjaxHandler()
    {
        $posts = $_POST['posts'];

        foreach ($posts as $post) {
            wp_update_post([
                'ID' => $post['id'],
                'menu_order' => $post['order'],
            ]);
        }

        wp_send_json(1);
        wp_die();
    }

    protected function getPosts()
    {
        return $this->postType->model::orderBySortOrder()->withTitle()->get();
    }

    protected function renderPage()
    {
        $html =
        <<<'blade'
            <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.4.0/Sortable.min.js"></script>
            <style media="screen">
                .heading {
                    margin: 30px 0;
                }
                .list {
                    padding-right: 15px;
                }
                .list * {
                    box-sizing: border-box;
                }
                .list li {
                    background: #fff;
                    padding: 10px;
                    cursor: move;
                    max-width: 100%;
                }
                div.updated {
                    margin: 15px 0;
                }
                #success {
                    display: none;
                }
                #success.isActive {
                    display: block;
                }
            </style>

            <div>
                <h1 class="heading">
                    Sort
                </h1>

                <div class="message updated fade" id="success">
                    <p>
                        Updated!
                    </p>
                </div>

                <ul id="sort-posts" class="list sortable">
                    @foreach ($posts as $post)
                        <li data-id="{{ $post->id }}">
                            {{ $post->title }}
                        </li>
                    @endforeach
                </ul>


                <button class="button-primary" id="savePostsOrder">
                    Save
                </button>
            </div>

            <script type="text/javascript">
                var button = document.querySelector('#savePostsOrder');
                var list = document.querySelector(".sortable");
        
                button.addEventListener('click', function() {
                    updateSortOrder(list);
                });
        
                Sortable.create(list, {
                    group: "sorting",
                    sort: true,
                });
        
                function updateSortOrder(list) {
                    var listItems = list.children;
        
                    itemsToUpdate = [];
        
                    for (var i = 0; i < listItems.length; i++) {
                        var id = listItems[i].dataset.id;
        
                        var item = {
                            id: id,
                            order: i + 1
                        };
        
                        itemsToUpdate.push(item);
                    }
        
                    jQuery.ajax({
                    type: 'POST',
                    dataType: 'json',
                    url: ajaxurl,
                    data: {
                        'action': 'updatePostsOrder',
                        'posts': itemsToUpdate,
                    },
                    success: function(result) {
                            if (result) {
                                var notice = document.querySelector('#success');
                                notice.classList.add('isActive');
                                setTimeout(function() {
                                    notice.classList.remove('isActive');
                                }, 5000);
                            }
                    }
                    });
                }
            </script>
        blade;

        echo Blade::render($html, [
            'posts' => $this->getPosts(),
        ]);
    }
}
