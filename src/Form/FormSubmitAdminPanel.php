<?php

namespace HumbleCore\Form;

use HumbleCore\Support\Facades\Action;
use Illuminate\Support\Facades\Blade;

class FormSubmitAdminPanel
{
    public static function register()
    {
        Action::add('admin_menu', [FormSubmitAdminPanel::class, 'registerAdminMenu']);

        Action::add('wp_ajax_removeFormSubmit', [FormSubmitAdminPanel::class, 'handleRemoveSubmit']);
    }

    public static function registerAdminMenu()
    {
        add_menu_page(
            'Form submits',
            'Form submits',
            'edit_pages',
            'form-submits',
            [FormSubmitAdminPanel::class, 'renderPage'],
            'dashicons-testimonial',
            100,
        );
    }

    public static function handleRemoveSubmit()
    {
        FormSubmit::remove(request('id'));

        wp_send_json('success');
        wp_die();
    }

    public static function renderPage()
    {
        $exportUrl = null;

        $submits = FormSubmitModel::status('private')->withTitle()->withDate()->get();

        $html =
        <<<'blade'
            <style>
                .card {
                    max-width: calc(100% - 3rem);
                    width: 60rem;
                    min-width: 0;
                    padding: 1.2rem 1.5rem;
                    margin-top: 0.75rem;
                }

                .card__header {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    cursor: pointer;
                }

                .card__title {
                    margin: 0;
                    margin-bottom: 0.4rem;
                }

                .card__date {
                    margin: 0;
                }

                .card__chevron {
                    width: 1.8rem;
                    height: 1.8rem;
                    flex-shrink: 0;
                    margin-left: 1.5rem;
                    transition: 0.25s;
                }

                .card.isOpen .card__chevron {
                    transform: rotate(0.5turn);
                }

                .card__body {
                    display: none;
                    padding-top: 1rem;
                }

                .card__deleteButton {
                    border: 0;
                    box-shadow: none;
                    background-color: transparent;
                    width: 1.4rem;
                    height: 1.4rem;
                    padding: 0;
                    position: absolute;
                    top: 50%;
                    margin-top: -0.7rem;
                    right: -3rem;
                    cursor: pointer;
                    transition: 0.25s;
                }
    
                .card__deleteButton:hover {
                    opacity: 0.5;
                }
    
                .card__deleteButton:focus {
                    outline: 0;
                }
    
                .card__deleteButton svg {
                    width: 100%;
                    height: 100%;
                }
            </style>

            <div class="wrap">
                <div style="margin-bottom: 15px">
                    <h1 class="wp-heading-inline">
                        Form submits
                    </h1>

                    @if ($exportUrl)
                        <a href="#" target="_blank" class="page-title-action">
                            Export
                        </a>
                    @endif

                    <hr class="wp-header-end">
                </div>

                @foreach($submits as $submit)
                    <div class="card">
                        <div class="card__header">
                            <div class="card__headerText">
                                <h3 class="card__title">
                                    {{ $submit->title }}
                                </h3>

                                <p class="card__date">
                                    {{ $submit->date }}
                                </p>
                            </div>

                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="card__chevron"><polyline points="6 9 12 15 18 9"></polyline></svg>
                        
                            <button
                                data-id="{{ $submit->id }}"
                                type="button"
                                class="card__deleteButton"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                            </button>
                        </div>

                        <div class="card__body">
                            <p>
                                {!! $submit->content !!}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>

            <script>
                jQuery('.card__deleteButton').click(function (event) {
                    event.stopPropagation();

                    if (!window.confirm('Confirm that you want to proceed with the removal')) {
                        return;
                    }

                    var id = jQuery(this).data('id');
                    jQuery(this).closest('.card').remove();

                    jQuery.ajax({
                        type: 'POST',
                        dataType: 'json',
                        url: ajaxurl,
                        data: {
                            'action': 'removeFormSubmit',
                            'id': id,
                        },
                        success: function(result) {
                            console.log(result);
                        }
                    });
                });

                jQuery('.card__header').click(function () {
                    var card = jQuery(this).closest('.card');

                    card.toggleClass('isOpen');
                    card.find('.card__body').slideToggle(250);
                });
            </script>
        blade;

        echo Blade::render($html, [
            'exportUrl' => $exportUrl,
            'submits' => $submits,
        ]);
    }
}
