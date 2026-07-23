import Alpine from "alpinejs";
import "./bootstrap";

import {
    ClassicEditor,
    Essentials,
    Paragraph,
    Bold,
    Italic,
    Link,
    Heading,
    SourceEditing,
} from "ckeditor5";
import "ckeditor5/ckeditor5.css";

window.ClassicEditor = ClassicEditor;
window.CKEditorPlugins = {
    Essentials,
    Paragraph,
    Bold,
    Italic,
    Link,
    Heading,
    SourceEditing,
};

window.Alpine = Alpine;
Alpine.start();
