var frame, gallry;

;(function($){
	$(document).ready(function(){

		$('#omb_date').datepicker({
			changeMonth: true,
			changeYear: true
		});

		var img_url = $("#omb_img_url").val();
		if (img_url) {
			$(".omb_img_container").html(`<img style="width:80px;" src="${img_url}">`);
		}

		var gallry_url = $("#omb_gallery_url").val();
		gallry_url = gallry_url ? gallry_url.split(';') : [] ;
		for(i in gallry_url ){
			$(".omb_gallery_container").append(`<img style="width:80px;" src="${gallry_url[i]}">`);
		}

		$("#omb_image_btn").on("click",function(){

			if (frame) {
				frame.open();
				return false; // Prevent opening object each time click on button
			}

			frame = wp.media({
				title: "Select an image",
				button: {
					text: "Upload"
				},
				multiple:false
			});

			frame.on("select", function(){
				var attachment = frame.state().get('selection').first().toJSON();
				$("#omb_img_id").val(attachment.id);
				$("#omb_img_url").val(attachment.url);
				$(".omb_img_container").html(`<img src="${attachment.sizes.thumbnail.url}">`);
			})


			frame.open();
			return false;
		})

		$("#omb_gallery_btn").on("click",function(){

			if (gallry) {
				gallry.open();
				return false; // Prevent opening object each time click on button
			}

			gallry = wp.media({
				title: "Select images",
				button: {
					text: "Upload"
				},
				multiple:true
			});

			gallry.on("select",function(){
				var attachments = gallry.state().get('selection').toJSON();
				var img_id = [];
				var img_url = [];
				$(".omb_gallery_container").html("");
				for(i in attachments){
					var attachment = attachments[i];
					img_id.push(attachment.id);
					img_url.push(attachment.url);

					$(".omb_gallery_container").append(`<img src="${attachment.sizes.thumbnail.url}">`);
				}
				$("#omb_gallery_id").val(img_id.join(';'));
				$("#omb_gallery_url").val(img_url.join(';'));
			})


			gallry.open();
			return false;
		})

		$(".js-example-basic-multiple").select2();


	});
})(jQuery);