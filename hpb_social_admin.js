jQuery(document).ready(function($){
	$('#hpb_social_tabs').tabs();

	if($("#socialbuttonlist").children("li").length == 0 ) {
		$("#socialbuttonlist").addClass("guidance_drop");
	}

	$("#socialbuttonlist").sortable({
		connectWith: '#trashbox',
		update: function(){
			var data=[];
			$("li","#socialbuttonlist").each(function(i,v){
				data.push($(v).children("img").attr("id"));
			});
			$('#order').attr("value", data.toString());
			if($("#socialbuttonlist").children("li").length == 0 ) {
				$("#socialbuttonlist").addClass("guidance_drop");
			} else {
				$("#socialbuttonlist").removeClass("guidance_drop");
			}
		}
	});
	$("#trashbox").droppable({
		accept: '#socialbuttonlist > li',
         	activeClass: 'ui-state-active',
         	hoverClass: 'ui-state-hover',
		 over: function(ev, ui) {
	 		$(".over", this).fadeIn();
		},
		 out: function(ev, ui) {
	 		$(".over", this).fadeOut();
		},
		 drop: function(event, ui) {
	 		$(".over", this).fadeOut();
			deleteImage(ui.draggable,ui.helper);
		}
 	});

	function deleteImage($draggable,$helper){
 		$draggable.remove();
 	};

	$("ul.buttonTypes > li").draggable({
	 	 helper: 'clone',
		 connectToSortable: '#socialbuttonlist'
	});
});