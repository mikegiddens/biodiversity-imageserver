function USGSOverlay(bounds, image, map) {
			this.bounds_ = bounds;
			this.image_ = image;
			this.map_ = map;
			this.div_ = null;
			this.setMap(map);
}USGSOverlay.prototype = new google.maps.OverlayView();
 
	USGSOverlay.prototype.onAdd = function() {
		var div = document.createElement('DIV');
		div.style.border = "none";
		div.style.borderWidth = "0px";
		div.style.position = "absolute";

		var img = document.createElement("img");
		img.src = this.image_;
		img.style.width = "100%";
		img.style.height = "100%";
		div.appendChild(img);
		this.div_ = div;
		var panes = this.getPanes();
		panes.overlayImage.appendChild(this.div_);
  }

  USGSOverlay.prototype.draw = function() {

   var overlayProjection = this.getProjection();

   var sw = overlayProjection.fromLatLngToDivPixel(this.bounds_.getSouthWest());
   var ne = overlayProjection.fromLatLngToDivPixel(this.bounds_.getNorthEast());

    var div = this.div_;
    div.style.left = sw.x + 'px';
    div.style.top = ne.y + 'px';
    div.style.width = (ne.x - sw.x) + 'px';
    div.style.height = (sw.y - ne.y) + 'px';
  }

USGSOverlay.prototype.onRemove = function() {
    this.div_.parentNode.removeChild(this.div_);
  }
