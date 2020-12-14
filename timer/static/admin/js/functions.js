var type_change = new Array('date','time','special');
function timer_type_change(type,special_type) {
	for(i=0;i<type_change.length;i++) {
		$(".block_type_"+type_change[i]).hide();
	}
	$(".block_type_"+type).show();
	if(type=='date') {
		$(".not_block_type_date").hide();
	} else {
		$(".not_block_type_date").show();
	}
	if(type=='special') {
		for(i=2;i<=3;i++) {
			$(".block_special_"+i).hide();
		}
		$(".block_special_"+special_type).show();
	}
	return false;
}
$("#type_select").change(function() {
	type = $(this).val();
	timer_type_change(type,1);
});
$("#special_select").change(function() {
	type = $(this).val();
	timer_type_change('special',type);
});
