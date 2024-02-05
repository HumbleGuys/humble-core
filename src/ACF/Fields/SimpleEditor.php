<?php

namespace HumbleCore\ACF\Fields;

use Extended\ACF\Fields\WYSIWYGEditor;

class SimpleEditor
{
    public static function make($label, $name): WysiwygEditor
    {
        return WYSIWYGEditor::make($label, $name)->disableMediaUpload()->toolbar(['bold', 'italic', 'underline', 'strikethrough', 'undo', 'redo', 'link']);
    }
}
