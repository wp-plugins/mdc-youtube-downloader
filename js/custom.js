$ = new jQuery.noConflict();
$(document).ready(function($) {
    if (typeof tinymce !== 'undefined'){
        tinymce.create('tinymce.plugins.mdc_plugin', {
            init : function(ed, url) {
                    ed.addCommand('mdc_insert_shortcode', function() {
                        content = '[youtube_downloader_form]';
                        tinymce.execCommand('mceInsertContent', false, content);
                    });
                ed.addButton('mdc_button', {title : 'Insert Youtube Downloader Form', cmd : 'mdc_insert_shortcode', image: wp_ulrs.yt_icon });
            },   
        });

        tinymce.PluginManager.add('mdc_button', tinymce.plugins.mdc_plugin);
    }

    // toggle help texts
    $(".mdc_help_icon").click(function(){
        var par = $(this).parent();
        $(".mdc_help", par).slideToggle();
    })

    // toggle height, width input
    $("#mdc_show_thumbnail").change(function(){
        if($(this).is(":checked")){
            $(".thumbnail_row").show();
        }
        else{
            $(".thumbnail_row").hide();
        }
    })
});