
Ext.namespace('Ext');
Ext.namespace('Ext.ux');


Ext.ux.XTemplate = Ext.extend( Ext.XTemplate,  {
				staticIndex:0
			,	testMirror: function(value){
					if(typeof(this.mirrorObj) != 'undefined'){
						var mirrorIndex = this.getMirror(value);
						if(typeof (mirrorIndex) != 'undefined'){
							if (this.staticIndex+1 > this.mirrorObj[mirrorIndex].mirrors.length){
								this.staticIndex = 0 ;
								return value;
							}else{ 
								var val = value.replace(this.mirrorObj[mirrorIndex].main,this.mirrorObj[mirrorIndex].mirrors[this.staticIndex]);//Config.mirrors[0].mirrors
								this.staticIndex++;
								return val;
							}
						}else{
							return value;
						}
					}else{
						return value;
					}
				}	
			,	getMirror: function(value){
					for (var i = 0; i< this.mirrorObj.length;i++){
						var index = value.search(this.mirrorObj[i].main);
							if( index!= -1 ){
								if(this.mirrorObj[i].mirrors != null){
									if(!Ext.isEmpty(this.mirrorObj[i].mirrors)){
										return i;
									}
								}		
							}
					}; 
				}					
    		,	setMirror: function(value){
					if(value != null){
						if(!Ext.isEmpty(value)){
							this.mirrorObj = value;
						}
					}	
				}	
			,	convDate:function(value){
					if (value == '0000-00-00 00:00:00') 
						return String.format('');
					else {
						var dt = Date.parseDate(value, "Y-m-d H:i:s", true);
						var dt1 = new Date(dt);
						var dt2= dt1.format('d-M-Y');
						return dt2;
					}
				}	
	});
