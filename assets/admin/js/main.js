var frame;
var gallery_frame;
;(function ($) {
    $(document).ready(function () {
        let image_url = $("#image_url").val();
        if (image_url) {
            $("#image-container").html(`<img class="media-image" src="${image_url}" />`);
        }

        let gallery_urls = $("#omb_gallery_url").val();
        gallery_urls = gallery_urls ? gallery_urls.split(";") : [];
        for (i in gallery_urls) {
            let url = gallery_urls[i];
            $("#images-container").append(`<img style="margin-right: 10px;" class="media-image" src="${url}" />`);

        }

        $(".omb_datepicker").datepicker();

        $("#upload_image").on("click", function () {
            if (frame) {
                frame.open();
            } else {

                frame = wp.media({
                    title: "Select Image",
                    button: {
                        text: "Insert image"
                    },
                    multiple: false
                });
            }

            frame.on("select", function () {
                let attachment = frame.state().get("selection").first().toJSON();
                console.log(attachment);
                $("#omb_image_id").val(attachment.id);
                $("#omb_image_url").val(attachment.sizes.thumbnail.url);
                $("#image-container").html(`<img class="media-image" src="${attachment.url}" />`);

            });

            frame.open();
            return false;
        });


        $("#upload_gallery").on("click", function () {
            if (gallery_frame) {
                gallery_frame.open();
            } else {
                gallery_frame = wp.media({
                    title: "Select Images",
                    button: {
                        text: "Insert Images"
                    },
                    multiple: true
                });
            }


            gallery_frame.on("select", function () {
                console.clear();
                let image_ids = [];
                let image_urls = [];
                let attachments = gallery_frame.state().get("selection").toJSON();
                // console.log(attachments);
                $("#images-container").html('');
                $("#omb_gallery_id").val('');
                $("#omb_gallery_url").val('');
                for (i in attachments) {
                    let attachment = attachments[i];
                    image_ids.push(attachment.id);
                    image_urls.push(attachment.sizes.thumbnail.url);
                    $("#images-container").append(`<img style="margin-right: 10px;" class="media-image" src="${attachment.url}" />`);
                }

                console.log(image_ids, image_urls);

                $("#omb_gallery_id").val(image_ids.join(";"));
                $("#omb_gallery_url").val(image_urls.join(";"));

            });

            gallery_frame.open();
            return false;
        })

    });
})(jQuery);