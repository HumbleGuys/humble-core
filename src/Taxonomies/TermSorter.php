<?php

namespace HumbleCore\Taxonomies;

use HumbleCore\Support\Facades\Action;
use Illuminate\Support\Facades\Blade;

class TermSorter
{
    public $taxonomy;

    public function __construct(Taxonomy $taxonomy)
    {
        $this->taxonomy = $taxonomy;

        Action::add('admin_menu', function () {
            $this->registerPages();
        });

        Action::add('wp_ajax_updateTermsOrder', function () {
            $this->registerAjaxHandler();
        });
    }

    public function registerPages()
    {
        foreach ($this->taxonomy->postTypes as $postType) {
            $this->registerPage($postType);
        }
    }

    protected function registerAjaxHandler()
    {
        $items = $_POST['items'];
        $taxonomy = $_POST['taxonomy'];

        foreach ($items as $item) {
            update_option($taxonomy.'_'.$item['id'].'_sortorder', $item['order']);
        }

        wp_send_json(1);
        wp_die();
    }

    public function registerPage($postType)
    {
        $parentSlug = $postType === 'post' ? 'edit.php' : 'edit.php?post_type='.$postType;

        $label = __('Sort', 'humble_core').' '.$this->taxonomy->labels['name'];

        add_submenu_page(
            $parentSlug,
            $label,
            $label,
            'edit_others_posts',
            "{$this->taxonomy->name}-sort",
            function () {
                $this->renderPage();
            }
        );
    }

    public function getTerms()
    {
        return $this->taxonomy->model::orderBySortOrder()->withEmpty()->get();
    }

    public function renderPage()
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

                <ul id="sort-terms" class="list sortable">
                    @foreach ($terms as $term)
                        <li data-id="{{ $term->id }}">
                            {{ $term->name }}
                        </li>
                    @endforeach
                </ul>


                <button class="button-primary" id="saveTermsOrder">
                    Save
                </button>
            </div>

            <script type="text/javascript">
                var button = document.querySelector('#saveTermsOrder');
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
                        'action': 'updateTermsOrder',
                        'taxonomy': '{{ $taxonomyName }}',
                        'items': itemsToUpdate,
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
            'taxonomyName' => $this->taxonomy->name,
            'terms' => $this->getTerms(),
        ]);
    }
}
