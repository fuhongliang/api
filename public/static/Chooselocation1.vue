<template>
	<div class="chooselocation" ref="homePage">
	  <el-amap vid="amapDemo"  :center="center" :map-manager="amapManager" :zoom="zoom" :events="events" class="amap-demo">
      </el-amap>
	</div>
</template>

<script>
	import { AMapManager } from 'vue-amap'
	export default{
		data(){
			return {
				msg:"选择位置",
				ak:"efhNFs0eQd5NA9cLUnNeIt4XwK6xvBVW",
				zoom: 12,
			    center: [121.59996, 31.197646],
			    amapManager,
			    events: {
					init(map) {
						AmapUI.loadUI(['overlay/SimpleMarker'],function(SimpleMarker){
							 const marker = new SimpleMarker({
							  iconLabel: 'A',
							  iconStyle: 'red',
							  map: map,
							  position: map.getCenter()
							});
						})
					}
				},
		  }
		},
		mounted(){
			var height = window.innerHeight;
			var devicePixelRatio = window.devicePixelRatio;
			var isAndroid = window.navigator.appVersion.match(/android/gi);
			var isIPhone = window.navigator.appVersion.match(/iphone/gi);
			    if (isIPhone) {
			        if (devicePixelRatio >= 3) {
						// isIPhone  dpr = 3
			            this.$refs.homePage.style.height =height + "px";
			        } else if (devicePixelRatio >= 2){
						// isIPhone dpr = 2
			            this.$refs.homePage.style.height = height + "px";
			        } else {
						// isIPhone dpr = 1
			            this.$refs.homePage.style.height = height + "px";
			        }
			    } else {
			        //  isAndroid dpr = 1
					this.$refs.homePage.style.height = height + "px";
			    }
		}
	}
</script>

<style scoped lang="stylus">
	div.chooselocation
		width:totalWid
		overflow:hidden
</style>
