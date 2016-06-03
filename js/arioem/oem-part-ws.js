function layoutToolTip(){
	jQuery("#ariHotSpotToolTipInfo div:eq(3)").attr('class', 'tooltipStockStat');
	jQuery("#ariHotSpotToolTipPartNumberLabel").text('Part#');
	jQuery("#ariHotSpotToolTipQtyLabel").text('Qty:');
}

function adjustTable() {
	var detailsTableWatcher = setInterval(function() {
		if(document.getElementById('ariPartList')) {
			layoutTable();
			clearInterval(detailsTableWatcher);
		} else {
			// just wait
		}
	}, 1000);
}

function layoutTable() {
	if(jQuery(".listTD:eq(0)").text().toLowerCase() != 'tag') {
		jQuery("#ariPartList").css({display: 'none'});

		jQuery(".listTD:eq(7)").remove(); // stock
		jQuery(".listTD:eq(5)").hide(); // online price
	//	jQuery(".listTD:eq(4)").remove(); // MSRP

		jQuery('.ariPLCart').remove();
	//	jQuery('.ariPLMSRP').remove();
		jQuery('.ariPLPrice').hide(); // jQuery('.ariPLPrice').remove();

		var tbl = jQuery('#ariPartList table');
		jQuery.moveColumn(tbl, 0, 7); // Move checkbox to last column.

		jQuery(".listTD:eq(0)").attr('width', '5%');
		jQuery(".listTD:eq(6)").attr('width', '5%');
	}

	var partNumber = location.search.substr(1);
	if(partNumber) {
		jQuery('span:contains("' + partNumber + '")').closest('tr').children('td').css({
				background: '#FFB872',
				'font-weight': 'bold'
			});
	}

	jQuery('#ariPartList').css({display: 'block'});
}

function createSidebar() {
	var myVar =  setInterval(function() {
		var clonedUL;

		if (jQuery('#ari_Assemblies_jl').is(':has(ul)') && jQuery( "#assembly-menu" ).is(':has(ul)') == false){
			jQuery( "#assembly-menu" ).append( "<div id='sidebarTitle'>CURRENTLY SHOPPING PARTS FOR:</div>");

			var queryParts = decodeURIComponent(location.hash).substr(2).replace(/_/g, ' ').split('/');
			var modelNameParts = queryParts[1].split('VIN#');
			var modelName = modelNameParts[0].replace(/^[\s,]+|[\s,]+$/gm,'');
			var modelTitle = queryParts[0] + '<br/>' + modelName;

			jQuery( "#assembly-menu" ).append( "<div id='menuBoxTitle'><h5>" +  modelTitle + "</h5><a id='changeMachine' href='oem-parts.html'>CHANGE MACHINE</a> <!-- <a href='#'>MY GARAGE</a> --></div>");
			jQuery( "#assembly-menu" ).append( "<h2>PARTS CATEGORIES</h2>");

			var options = document.getElementById('ari_Assemblies');
			var categoriesHTML = '';
			for(var i=0; i<options.length; i++) {
				categoriesHTML += '<li value="' + options[i].value +'" onclick="assemblyClick(' + options[i].value +')">' + options[i].innerHTML + '</li>';
			}
			categoriesHTML = '<ul>' + categoriesHTML + '</ul>';
			jQuery('#assembly-menu').append(categoriesHTML);

//console.log('Sidebar appended!');
			createTriggerNewModelBtn();
			createTriggerDropdownBox();
			clearTimeout(myVar);

			attachTitle();
			addClassCurrentItem();
		} else if (jQuery('#ari_Assemblies_jl').is(':has(ul)') && jQuery( "#assembly-menu" ).is(':has(ul)')){
//console.log('Sidebar exists');
			createTriggerNewModelBtn();
			createTriggerDropdownBox();
			clearTimeout(myVar);

			attachTitle();
			addClassCurrentItem();
		} else {
//console.log('Waiting for sidebar...');
			addTopRightButtons();
		}

	}, 3000);

	adjustTable();
}

function addFullScrnBtn(){
	jQuery( ".ariMultiCartWrapper:eq(3)").remove();
	jQuery( ".ariMultiCartWrapper:eq(2)").remove();

	jQuery( ".wishlist-btn").remove();
	jQuery('<img class="wishlist-btn" src="/media/images/oem-parts/btn-wishlist.jpg" title="Wishlist" style="margin-right: 7px;" />').insertBefore('.ariMultiCartWrapper .ariparts_btnMultiCart');
}

function addTopRightButtons(){
	jQuery('.oem-top-right-buttons').remove();
	jQuery('<div class="oem-top-right-buttons"><img src="/media/images/oem-parts/oem-chat-specialist.jpg" title="Chat with OEM Specialist" onclick="jQuery(\'.live-chat-button img\').click()" /><br/><img src="/media/images/oem-parts/parts-finder-tips.jpg" title="Parts Finder Tips" /></div>').insertAfter( "#ariAssemblyList" );

	addFullScrnBtn();
	addBannersBelowSidebar();
	removeBannersBottom();
	changeText();
//	layoutTable();
	layoutToolTip();
	addShippingFYI();
}

function addLowerLeftBtn(){
	jQuery('.oem-lower-left-buttons').remove();
	jQuery('<div class="oem-lower-left-buttons"><img src="/media/images/oem-parts/print.png" title="Print" /><img src="/media/images/oem-parts/link.png" title="Link" style="margin-left: 16px; margin-bottom: 4px;" /></div>').insertBefore( "#ariInfoZoom" );
}

function addBannersBelowSidebar(){
	jQuery('.sideBanners').remove();

	if (jQuery( "#assembly-menu" ).is(':has(ul)')) {
		jQuery( "#left-sidebar" ).append('<div class="sideBanners"><img src="/media/images/oem-parts/best-prices.jpg" title="Best Prices" style="margin-bottom: 1em;" /><br/><img src="/media/images/oem-parts/call-us.jpg" title="Call Us" /></div>');
	}
}

function removeBannersBottom(){
	if (jQuery( "#assembly-menu" ).is(':has(ul)')) {
		jQuery('#bannersBottom').hide();
	} else {
		jQuery('#bannersBottom').show();
	}
}

function changeText(){
	jQuery('#ariAssemblyLabelContent').text('SELECT PARTS SCHEMATIC:');
}

function addClassCurrentItem() {
	jQuery('#assembly-menu li').removeClass();
	var index = jQuery('#ari_Assemblies option:selected').attr('value');
	jQuery('#assembly-menu li[value="' + index + '"]').addClass('currentItem');
	addTopRightButtons();
	changeText();
}

function attachTitle() {
	var title = getTitle();
	var brand = extractBrandFromURL();
	var imgFilename = brand.toLowerCase() +'.jpg';
	var fullTitle = '<img height="71" src="/media/images/' + imgFilename + '" /><span class="brandTitle">' + title + '</span>';
	jQuery('#brandTitleBox').empty();
	jQuery('#brandTitleBox').append(fullTitle);
}

function getTitle(){
	var titleTxt = jQuery('#ariparts_lblModelName').text();
	var partsTxt = jQuery('#aari_Assemblies_jl').text();

	return titleTxt + ' OEM ' + partsTxt + ' PARTS';
}

function extractBrandFromURL() {
	var fullURL = location.hash;
	var myArray = fullURL.split('/');
	var brand = myArray[1];
	return brand;
}

function createTriggerNewModelBtn(){
	jQuery('#ariBackToSearch').click(function() {
		jQuery( "#assembly-menu" ).empty();
		jQuery('#ari_Assemblies_jl ul').remove();
		createSidebar();
//		layoutTable();
	});
}

function createTriggerDropdownBox(){

	var myVar2 =  setInterval(function(){
		if (jQuery('#ari_Assemblies_jl').is(':has(ul)')) {
			jQuery('#ari_Assemblies_jl li').click(function() {
				createSidebar();
				createTriggerNewModelBtn();
			});
			createTriggerNewModelBtn();
			clearTimeout(myVar2);
			addTopRightButtons();
		} else {
			addTopRightButtons();
		}
	}, 3000);

}


function assemblyClick(index) {
//	jQuery("#ariPartList").css({display: 'none'});
	jQuery('#ari_Assemblies_jl li[rel="' + index + '"]').click();
	createSidebar();
	createTriggerNewModelBtn();
	createTriggerDropdownBox();
	adjustTable();
};


function addShippingFYI() {
	jQuery('.oem-fyi').remove();
	jQuery('.oem-howto').remove();
	if(!jQuery('.oem-fyi').length) {
		jQuery('<div class="oem-fyi">Most OEM parts are stocked in our CA warehouse and will be shipped out within 1-3 business days. In the event that an OEM part is not available at our warehouse you will be promptly notified with an updated shipping date.</div><div class="oem-howto">HOW TO USE<br/>Use check boxes to select all desired items from image, chose quantity, and click &quot;Add to Cart&quot; Once items are added to cart they will be highlighted in green to indicate item has been selected.</div>').insertBefore('#ariPartList');
	}
};


function readCookie(name) {
	var nameEQ = encodeURIComponent(name) + "=";
	var ca = document.cookie.split(';');
	for (var i = 0; i < ca.length; i++) {
		var c = ca[i];
		while (c.charAt(0) === ' ') c = c.substring(1, c.length);
		if (c.indexOf(nameEQ) === 0) return decodeURIComponent(c.substring(nameEQ.length, c.length));
	}
	return null;
}

function eraseCookie(name) {
	var nameEQ = encodeURIComponent(name) + "=";
	var cookiePairs = document.cookie.split(';');
	for (var i = 0; i < cookiePairs.length; i++) {
		var c = cookiePairs[i];
		while (c.charAt(0) === ' ') c = c.substring(1, c.length);
		if (c.indexOf(nameEQ) === 0) {
			cookiePairs.splice(i, 1);
			document.cookie = cookiePairs.join(';');
			return;
		}
	}
}

jQuery(document).ready(function(){
	jQuery.moveColumn = function (table, from, to) {
		var rows = jQuery('tr', table);
		var cols;
		rows.each(function() {
			cols = jQuery(this).children('th, td');
			if (to < cols.length)
				cols.eq(from).detach().insertBefore(cols.eq(to));
			else
				cols.eq(from).detach().insertAfter(cols.last());
		});
	};

	createSidebar();
});

