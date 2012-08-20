Ext.define('BIS.view.ImageZoomTile', {
    extend: 'Ext.Component',
	alias: ['widget.tilelayer'],
    initComponent: function() {
        var me = this;
        // add string function
        String.format = function() {
            var s = arguments[0];
            for (var i = 0; i < arguments.length - 1; i++) {       
            var reg = new RegExp("\\{" + i + "\\}", "gm");             
            s = s.replace(reg, arguments[i + 1]);
            }
            return s;
        }
        //
        Ext.applyIf( me, {
        	zoomLevel: 5,
        	tileSize: 256,
        	tileTpl: '',
        	style: 'overflow:hidden; position:relative;'
        });
        me.callParent(arguments);
    },
    listeners: {
        afterrender: function() {
            this.tiles = [];
            this.xtpl = Ext.DomHelper.createTemplate({
                tag: 'img',
                src: Ext.BLANK_IMAGE_URL,
                width: 256,
                height: 256,
                cls: 'tile'
            });
            this.createTiles(this.zoomLevel);
            this.el.setWidth((this.tileSize * (Math.pow(this.zoomLevel, 2))) + 2);
        }
    },
    tiles: [],
    createTiles: function(zoomLevel) {
        this.zoomLevel = zoomLevel;
        var tileCount = Math.pow(zoomLevel, 2);
        for(var i=1; i<= tileCount; i++){
            this.tiles[i] = new Array();
            for(var j=1; j<= tileCount; j++){
                this.tiles[i][j] = this.xtpl.append(this.el.dom, [i, j]);
            }
        }
        this.lastDiv = Ext.DomHelper.append(this.el.dom, {tag: 'div', style: 'clear:both'});
    },
    removeTiles: function() {
        var tileCount = Math.pow(this.zoomLevel, 2);
        for(var i=1; i<= tileCount; i++){
            for(var j=1; j<= tileCount; j++){
                Ext.fly(this.tiles[i][j]).remove();
            }
        }
        Ext.fly(this.lastDiv).remove();
        this.tiles = new Array();
    },
    getTile: function(x, y){
        return this.tiles[x][y];
    },
    isBlankTile: function(x, y){
        return( (this.tiles[x][y].rendered) ? false : true );
    },
    setImage: function(x, y, url){
        if(this.tiles[x] && this.tiles[x][y] && !this.tiles[x][y].rendered){
            this.tiles[x][y].src = url;
            this.tiles[x][y].rendered = true;
        }
    },
    showTile: function(x, y) {
        if( this.tiles[x] && this.tiles[x][y] && !this.tiles[x][y].rendered ) {
            var tileCount = Math.pow(this.zoomLevel, 2);
            var imgName = ((x-1) * (tileCount)) + (y-1);
            var url = String.format(this.tileTpl, x, y, this.zoomLevel, imgName);
            this.setImage(x, y, url);
        }
    },
    showTilesByRange: function( topLeft, bottomRight ) {
        for(var x = topLeft.row; x <= bottomRight.row ; x++){
            for(var y = topLeft.column; y <= bottomRight.column; y++){
                this.showTile(x,y);
            }
        }
    },
    showAllTiles: function(){
        var tileCount = Math.pow(this.zoomLevel, 2);
        for(var i=1; i<= tileCount; i++){
            for(var j=1; j<= tileCount; j++){
                var imgName = ((i-1) * (tileCount)) + (j-1);
                var url = Ext.util.Format.format(this.tileTpl, i, j, this.zoomLevel, imgName);
                this.setImage(i, j, url);
            }
        }
    },
    hideTile: function(x, y){
        this.setImage(x, y, Ext.BLANK_IMAGE_URL);
    }
});
