<template>
	<div class="choose_map">
		<baidu-map :ak="ak" :center="center" :zoom="zoom"  :min-zoom="30" :max-zoom="1" @ready="handler" @touchend="getPoint($event)"  ref="bdmap" :pinch-to-zoom="true" @zoomend="syncCenterAndZoom">
		<bm-geolocation :showAddressBar="true" :autoLocation="true"></bm-geolocation>
		    <bm-marker :position="{lng:center.lng, lat:center.lat}" :icon="{url:icon,size:{width:48,height:48}}"></bm-marker>
			<bm-local-search :keyword="keywordArr" :auto-viewport="true" :location="center" >
			</bm-local-search>
		  </baidu-map>
	</div>
</template>

<script>
	import bus from '../../assets/js/eventBus.js'
	import BaiduMap from "vue-baidu-map/components/map/Map.vue"
	import BmMarker from 'vue-baidu-map/components/overlays/Marker'
	import BmGeolocation from 'vue-baidu-map/components/controls/Geolocation.vue'
	import BmLocalSearch from "vue-baidu-map/components/search/LocalSearch.vue"
	import AutoComplete from "vue-baidu-map/components/others/AutoComplete.vue"
	
	export default {
		data(){
			return {
				msg:"选择位置",
				ak:"efhNFs0eQd5NA9cLUnNeIt4XwK6xvBVW",
				center: "新余",//当前经纬度
				zoom: 12,//地图显示大小
				location: "",//location
				keyword: "",//搜索关键词
				keywordArr:[],
				address:"",//地图中的选中地址
				current:0,//选中的index
				confirmedAddress:"",
				jgNameDialog: false,
				show: false,
				startY: 0, // 纵轴方向初始化位置
				scrollToX: 0,
				scrollToY: 0,
				scrollToTime: 700,
				locationList:[],
				geolocation:null,
				initLocation: false,
				icon:"/static/img/x2/icon_lc_xhao@2x.png"
			}
		},
		
	}
	
	
	
	
</script>

<style scoped lang="stylus">
	div.choose_map
		width: 100%
		height: 100%
</style>
