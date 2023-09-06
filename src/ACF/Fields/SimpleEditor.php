<?php

namespace HumbleCore\ACF\Fields;

use Extended\ACF\Fields\WysiwygEditor;

class SimpleEditor
{
    public static function make($label, $name): WysiwygEditor
    {
        return WysiwygEditor::make($label, $name)->mediaUpload(false)->toolbar(['bold', 'italic', 'underline', 'strikethrough', 'undo', 'redo', 'link']);
    }
}
