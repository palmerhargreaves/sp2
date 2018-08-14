/**
 * Created by kostet on 23.03.2018.
 */

$(function() {
    $('#news_announcement, #news_text').tinymce({
        // Location of TinyMCE script
        script_url: '/js/backend/plugins/tiny_mce/tiny_mce.js',

        // General options
        theme: "advanced",
        plugins: "pagebreak,style,layer,table,save,advhr,advimage,advlink,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,advlist",
        language: 'ru',
        elements: 'absurls',
        relative_urls: false,

        // Theme options
        theme_advanced_buttons1: "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontsizeselect",
        theme_advanced_buttons2: "undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
        theme_advanced_buttons3: "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote",
        theme_advanced_toolbar_location: "top",
        theme_advanced_toolbar_align: "left",
        theme_advanced_statusbar_location: "bottom",
        theme_advanced_resizing: true,

        // Example content CSS (should be your site CSS)
        content_css: "/css/backend/tiny_mce/tiny.css",
        oninit: function () {
        }
    });
});
