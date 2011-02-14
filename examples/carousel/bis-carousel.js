Ext.ux.BisCarousel = Ext.extend(Ext.util.Observable, {

		constructor: function(el) {
			this.el = el;
			this.photoTemplate = new Ext.Template([
				'<a href="{href}" target="_blank" class="lightbox" title="{title}">',
						'<img src="{src}" border="0" alt="{Family} {ScientificName}" title="{Family} {ScientificName}">',
				'</a>'
			]);
	
			this.carousel = new Ext.ux.Carousel(el, {
					itemSelector: 'a.lightbox'
				,	interval: 3
				,	width: 225
				,	height: 280
				,	autoPlay: true
				,	showPlayButton: true
				,	pauseOnNavigate: true
				,	freezeOnHover: true
				,	transitionType: 'fade'
	//				navigationOnHover: true       
			});
				
		}

	,	loadPhotos: function(params) {						
			Ext.ux.JSONP.request(params.wsUrl, {
					callbackKey: 'callback'
				,	params: params
				,	callback: this.updatePhotos
				,	scope: this
			});
		}
	
	,	updatePhotos: function(data) {
			Ext.select('#combo-carousel > p').remove();                    
			this.carousel.clear();
			Ext.each(data.data, function(item) {																 
				item.src = item.path + item.barcode + "_m.jpg";
				item.href = item.path + item.filename;
				item.title = item.barcode;
				this.carousel.add(this.photoTemplate.append(this.el, item));
			}, this);	
			this.carousel.refresh().play();
		}

});