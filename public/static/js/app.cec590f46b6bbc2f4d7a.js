webpackJsonp([1],{"/B6U":function(t,e){},"2dnA":function(t,e){},"4ml/":function(t,e){},"5pmT":function(t,e){},"8DTz":function(t,e){},"8bjl":function(t,e){},"9KLQ":function(t,e){},G7iQ:function(t,e){},Jje9:function(t,e){},LXwj:function(t,e){},MPzD:function(t,e){},NHnr:function(t,e,a){"use strict";Object.defineProperty(e,"__esModule",{value:!0});var s=a("7+uW"),n={render:function(){var t=this.$createElement,e=this._self._c||t;return e("div",{attrs:{id:"app"}},[e("keep-alive",[e("router-view")],1)],1)},staticRenderFns:[]};var o=a("VU/8")({name:"App"},n,!1,function(t){a("/B6U")},"data-v-4d78df94",null).exports,i=a("/ocq"),r={render:function(){var t=this.$createElement,e=this._self._c||t;return e("div",{staticClass:"header"},[e("van-icon",{attrs:{name:"arrow-left"},on:{click:this.returnPage}}),this._v(" "),e("span",[this._v(this._s(this.title))])],1)},staticRenderFns:[]};var l=a("VU/8")({props:["title"],methods:{returnPage:function(){this.$router.go(-1)}},destroyed:function(){}},r,!1,function(t){a("hPlS")},"data-v-5f6bcdef",null).exports,c={data:function(){return{msg:"商家入驻",processStep:[{step:1,stepTitle:"店铺照片",stepContent:"需要拍出完整门匾，门框（ 建议正对门店2米处拍摄）",stepImg:"/static/img/x2/shilitu_bnt_menlianz@2x.png"},{step:2,stepTitle:"店铺内部环境",stepContent:"需要实际反映内部环境（收 银台，餐桌椅等）",stepImg:"/static/img/x2/shilitu_bnt_dianneiz@2x.png"},{step:3,stepTitle:"店铺LOGO",stepContent:"需要上传与店铺匹配的LOGO，大小不超过1MB",stepImg:"/static/img/x2/icon_bnt_llf@2x.png"},{step:4,stepTitle:"店铺手持身份证照",stepContent:"需要清晰展示人物五官及身 份证个人信息",stepImg:"/static/img/x2/icon_bnt_scsfz@2x.png"},{step:5,stepTitle:"营业执照",stepContent:"需要文字清晰可见，边框完 整，露出国徽",stepImg:"/static/img/x2/icon_bnt_yyzz@2x.png"}],clientHeight:null,clientWidth:null}},components:{Header:l},mounted:function(){this.clientHeight=window.screen.height,this.clientWidth=window.screen.width,console.log(this.clientHeight),console.log(this.clientWidth)}},v={render:function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",{ref:"homePage",staticClass:"enter"},[a("Header",{attrs:{title:t.msg}}),t._v(" "),a("div",{staticClass:"banner"},[a("router-link",{attrs:{to:"/store"}},[a("van-button",{attrs:{type:"info",size:"normal"}},[t._v("我要入驻")])],1)],1),t._v(" "),t._m(0),t._v(" "),a("div",{staticClass:"information"},[t._m(1),t._v(" "),t._l(t.processStep,function(e){return a("div",{staticClass:"informationDisplay"},[a("span",[t._v(t._s(e.step))]),t._v(" "),a("div",{staticClass:"text"},[a("p",[t._v(t._s(e.stepTitle))]),t._v(" "),a("p",[t._v(t._s(e.stepContent))])]),t._v(" "),a("div",{staticClass:"img"},[a("img",{attrs:{src:e.stepImg}})])])})],2)],1)},staticRenderFns:[function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",{staticClass:"process"},[a("p",[t._v("入驻流程")]),t._v(" "),a("ul",[a("li",[a("img",{attrs:{src:"/static/img/x2/icon_lc_tijiao@2x.png",alt:""}}),a("i",[t._v("提交申请")])]),t._v(" "),a("li",[a("img",{attrs:{src:"/static/img/x2/icon_lc_shenhe@2x.png",alt:""}}),a("i",[t._v("入驻审核")])]),t._v(" "),a("li",[a("img",{attrs:{src:"/static/img/x2/icon_lc_jf@2x.png",alt:""}}),a("i",[t._v("签署缴费")])]),t._v(" "),a("li",[a("img",{attrs:{src:"/static/img/x2/icon_lc_kd@2x.png",alt:""}}),a("i",[t._v("入驻经营")])])])])},function(){var t=this.$createElement,e=this._self._c||t;return e("div",{staticClass:"informationReady"},[e("p",[this._v("入驻前请准备好以下资料")]),this._v(" "),e("p",[this._v("建议您在证件齐全的时候完成入驻")])])}]};var h=a("VU/8")(c,v,!1,function(t){a("r/2J")},"data-v-629048ef",null).exports,p={data:function(){return{msg:"1/3 店铺信息",storeName:"",contactName:"",contactNumber:"",chooseLocation:"",taglocation:"",detailLocation:"",dialogImageUrl:"",dialogVisible:!1,clientHeight:"",regExpAll:!0,file:null,storeFrontImage:"plus",storeFrontPhoto:!1,storeInsidePhoto:!1,storeLogo:!1,imgPath1:"plus",imgPath2:"plus",imgPath3:"plus"}},components:{Header:l},methods:{showDialog1:function(){this.$dialog.alert({title:"门脸照示意图",message:'<img src="/static/img/x2/icon_bnt_dpzp@2x.png"><p>门店照需拍出完整门匾，门框，需在店铺开门营业状态下完成拍摄</p>',confirmButtonText:"知道了"})},showDialog2:function(){this.$dialog.alert({title:"店内照示意图",message:'<img src="/static/img/x2/icon_bnt_dnzp@2x.png"><p>店内照片需要实际反映内部情况（比如收银台、餐桌椅等）</p>',confirmButtonText:"知道了"})},showDialog3:function(){this.$dialog.alert({title:"LOGO示意图",message:'<img src="/static/img/x2/shilitu_bnt_klogo@2x.png"><p>店铺LOGO（支持jpg、jprg、png格式，大小不超过1mb）</p>',confirmButtonText:"知道了"})},onRead1:function(t){t.file.size>=1048576?(this.$toast("图片大小不能超过2M"),this.imgPath1="plus"):this.uploadImg1(t)},uploadImg1:function(t){var e=this,a=new FormData;a.append("file",t.file);this.$axios.post("/v3/image_upload",a,{headers:{"Content-Type":"multipart/form-data"}}).then(function(t){e.imgPath1="http://pqk40fvkr.bkt.clouddn.com/"+t.data.path,e.storeFrontPhoto=!0})},onRead2:function(t){t.file.size>=1048576?(this.$toast("图片大小不能超过2M"),this.imgPath2="plus"):this.uploadImg2(t)},uploadImg2:function(t){var e=this,a=new FormData;a.append("file",t.file);this.$axios.post("/v3/image_upload",a,{headers:{"Content-Type":"multipart/form-data"}}).then(function(t){e.imgPath2="http://pqk40fvkr.bkt.clouddn.com/"+t.data.path,e.storeInsidePhoto=!0})},onRead3:function(t){t.file.size>=1048576?(this.$toast("图片大小不能超过2M"),this.imgPath3="plus"):this.uploadImg3(t)},uploadImg3:function(t){var e=this,a=new FormData;a.append("file",t.file);this.$axios.post("/v3/image_upload",a,{headers:{"Content-Type":"multipart/form-data"}}).then(function(t){e.imgPath3="http://pqk40fvkr.bkt.clouddn.com/"+t.data.path,e.storeLogo=!0})},choosecity:function(){console.log(this.storeName),this.$router.push("/choosecity")},chooseLocationClick:function(){this.$router.push("/chooselo")},next:function(){this.regExpAll=!1,/^[\u4e00-\u9fa5A-Za-z0-9-_]{1,15}$/.test(this.storeName)&&""!=this.storeName||(this.regExpAll=!1),/^[\u4e00-\u9fa5A-Za-z0-9-_]{1,15}$/.test(this.contactName||""==this.storeName)||(this.regExpAll=!1),/\d{11}/.test(this.contactNumber||""==this.storeName)||(this.regExpAll=!1),""!=this.detailLocation&&""!=this.storeName||(this.regExpAll=!1),!0===this.regExpAll?(this.$router.push("/ident"),console.log(this.regExpAll)):(console.log(this.regExpAll),this.$toast("提交失败，请检查必填项信息是否填写完整"))}},mounted:function(){this.taglocation=JSON.parse(sessionStorage.getItem("location")),this.detailLocation=JSON.parse(sessionStorage.getItem("detailLocation")),console.log(this.detailLocation)},created:function(){}},u={render:function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",{ref:"homePage",staticClass:"store"},[a("Header",{attrs:{title:t.msg}}),t._v(" "),a("div",{staticClass:"part1"},[a("van-row",{staticClass:"store_title"},[a("van-col",{staticClass:"store_title_baseinfo",attrs:{span:6}},[t._v("基本信息")])],1),t._v(" "),a("van-row",{staticClass:"store_name"},[a("van-col",{attrs:{span:8}},[t._v("店铺名称"),a("i",[t._v("*")])]),t._v(" "),a("van-col",{attrs:{span:16}},[a("input",{directives:[{name:"model",rawName:"v-model",value:t.storeName,expression:"storeName"}],attrs:{type:"text",placeholder:"请输入店铺名字"},domProps:{value:t.storeName},on:{input:function(e){e.target.composing||(t.storeName=e.target.value)}}})])],1),t._v(" "),a("van-row",{staticClass:"contact_name"},[a("van-col",{attrs:{span:8}},[t._v("联系人姓名"),a("i",[t._v("*")])]),t._v(" "),a("van-col",{attrs:{span:16}},[a("input",{directives:[{name:"model",rawName:"v-model",value:t.contactName,expression:"contactName"}],attrs:{type:"text",placeholder:"请输入真实姓名"},domProps:{value:t.contactName},on:{input:function(e){e.target.composing||(t.contactName=e.target.value)}}})])],1),t._v(" "),a("van-row",{staticClass:"contact_number"},[a("van-col",{attrs:{span:8}},[t._v("联系人电话"),a("i",[t._v("*")])]),t._v(" "),a("van-col",{attrs:{span:16}},[a("input",{directives:[{name:"model",rawName:"v-model",value:t.contactNumber,expression:"contactNumber"}],attrs:{type:"text",placeholder:"请输入联系人电话"},domProps:{value:t.contactNumber},on:{input:function(e){e.target.composing||(t.contactNumber=e.target.value)}}})])],1),t._v(" "),a("van-row",{staticClass:"store_address"},[a("van-col",{attrs:{span:8}},[t._v("门店地址"),a("i",[t._v("*")])]),t._v(" "),a("van-col",{attrs:{span:2,offset:14}},[a("router-link",{attrs:{to:"/choosecity"}},[a("van-icon",{attrs:{name:"arrow"}})],1)],1)],1),t._v(" "),a("van-row",{staticClass:"store_location"},[a("van-col",{attrs:{span:8}},[t._v("标记店铺位置")]),t._v(" "),a("van-col",{attrs:{span:2,offset:14}},[a("router-link",{attrs:{to:"/chooselo"}},[a("van-icon",{attrs:{name:"arrow"}})],1)],1)],1),t._v(" "),a("van-row",{staticClass:"store_detail_address"},[a("van-col",{attrs:{span:24}},[a("input",{directives:[{name:"model",rawName:"v-model",value:t.taglocation,expression:"taglocation"}],attrs:{type:"text",placeholder:"详细地址 例：1号楼101室"},domProps:{value:t.taglocation},on:{input:function(e){e.target.composing||(t.taglocation=e.target.value)}}})])],1)],1),t._v(" "),a("div",{staticClass:"part2"},[a("van-row",{staticClass:"store_title"},[a("van-col",{attrs:{span:6}},[t._v("门店照片")])],1),t._v(" "),a("van-row",{staticClass:"store_front"},[a("van-col",{attrs:{span:7}},[t._v("门脸照"),a("i",[t._v("*")])]),t._v(" "),a("van-col",{attrs:{span:5,offset:1}},[a("van-uploader",{ref:"storeFront",attrs:{"after-read":t.onRead1}},[a("van-icon",{class:{active:1==t.storeFrontPhoto},attrs:{name:t.imgPath1}})],1)],1),t._v(" "),a("van-col",{attrs:{span:10}},[a("p",[t._v("一张真实美观的门脸照可以 提升店铺形象")]),t._v(" "),a("p",{on:{click:t.showDialog1}},[t._v("查看示例图")])])],1),t._v(" "),a("van-row",{staticClass:"store_inside"},[a("van-col",{attrs:{span:7}},[t._v("店内照"),a("i",[t._v("*")])]),t._v(" "),a("van-col",{attrs:{span:5,offset:1}},[a("van-uploader",{ref:"storeFront",attrs:{"after-read":t.onRead2}},[a("van-icon",{class:{active:1==t.storeInsidePhoto},attrs:{name:t.imgPath2}})],1)],1),t._v(" "),a("van-col",{attrs:{span:10}},[a("p",[t._v("简介干净的店内设施可以让用户放心舒心")]),t._v(" "),a("p",{on:{click:t.showDialog2}},[t._v("查看示例图")])])],1),t._v(" "),a("van-row",{staticClass:"store_logo"},[a("van-col",{attrs:{span:7}},[t._v("店铺LOGO"),a("i",[t._v("*")])]),t._v(" "),a("van-col",{attrs:{span:5,offset:1}},[a("van-uploader",{ref:"storeFront",attrs:{"after-read":t.onRead3}},[a("van-icon",{class:{active:1==t.storeLogo},attrs:{name:t.imgPath3}})],1)],1),t._v(" "),a("van-col",{attrs:{span:10}},[a("p",[t._v("店铺LOGO(支持jpg、jpeg、png格式,大小不超过1MB)")]),t._v(" "),a("p",{on:{click:t.showDialog3}},[t._v("查看示例图")])])],1)],1),t._v(" "),a("van-row",{staticClass:"store_btn_next"},[a("van-col",{attrs:{span:22,offset:1}},[a("van-button",{attrs:{type:"info",size:"large"},on:{click:t.next}},[t._v("下一步")])],1)],1)],1)},staticRenderFns:[]};var m=a("VU/8")(p,u,!1,function(t){a("tp2z")},"data-v-3eff1e9a",null).exports,d=a("mvHQ"),f=a.n(d),g={data:function(){return{msg:"选择城市",ProvinceValue:"请选择省…",CityValue:"请选择市…",AreaValue:"请选择区…",locationValue:"",clientHeight:"",ProvinceShow:!0,CityShow:!1,AreaShow:!1,ProvinceBtnShow:!0,CityBtnShow:!1,AreaBtnShow:!1,provinceIndex:0,cityIndex:0,areaIndex:0,chooseStatus:!1,startY:0,scrollToX:0,scrollToY:0,scrollToTime:700,items:[{id:1,province:"四川省",children:[{id:2,city:"成都市",children:["金堂县","青白江区","武侯区"]},{id:3,city:"乐山市",children:["金堂县","青白江区","武侯区"]},{id:4,city:"宜宾市",children:["金堂县","青白江区","武侯区"]}]},{id:5,province:"广东省",children:[{id:6,city:"深圳市",children:["宝安区","南山区","福田区"]},{id:7,city:"广州市",children:["宝安区","南山区","福田区"]},{id:8,city:"惠州市",children:["宝安区","南山区","福田区"]}]},{id:9,province:"湖南省",children:[{id:10,city:"长沙市",children:["宝安区","南山区","福田区"]},{id:11,city:"益阳市",children:["宝安区","南山区","福田区","宝安区","南山区","福田区","宝安区","南山区","福田区","宝安区","南山区","福田区","宝安区","南山区","福田区","宝安区","南山区","福田区","宝安区","南山区","福田区","宝安区","南山区","福田区","宝安区","南山区","福田区","宝安区","南山区","福田区","宝安区","南山区","福田区","宝安区","南山区","福田区","宝安区","南山区","福田区","宝安区","南山区","福田区","宝安区","南山区","福田区"]}]}]}},components:{Header:l},methods:{toggleProvince:function(){this.ProvinceShow=!0,this.CityShow=!1,this.AreaShow=!1},toggleCity:function(){this.ProvinceShow=!1,this.CityShow=!0,this.AreaShow=!1},toggleArea:function(){this.ProvinceShow=!1,this.CityShow=!1,this.AreaShow=!0},ProvinceClick:function(t,e){this.ProvinceValue=t.currentTarget.innerText,this.ProvinceShow=!1,this.ProvinceBtnShow=!0,this.CityBtnShow=!0,this.CityShow=!0,this.provinceIndex=e,this.chooseStatus=!1},CitiesClick:function(t,e){this.CityValue=t.currentTarget.innerText,this.AreaShow=!0,this.AreaBtnShow=!0,this.CityShow=!1,this.cityIndex=e},AreaClick:function(t,e){this.AreaValue=t.currentTarget.innerText,this.CityShow=!1,this.areaIndex=e,this.chooseStatus=!0,this.locationValue=this.ProvinceValue+this.CityValue+this.AreaValue,sessionStorage.setItem("location",f()(this.locationValue)),""!==this.locationValue&&this.$router.push("/store")},scrollTo:function(){this.$refs.scroll1.scrollTo(this.scrollToX,this.scrollToY,this.scrollToTime),this.$refs.scroll2.scrollTo(this.scrollToX,this.scrollToY,this.scrollToTime),this.$refs.scroll3.scrollTo(this.scrollToX,this.scrollToY,this.scrollToTime)}},mounted:function(){this.clientHeight=document.body.offsetHeight,console.log(this.clientHeight),1==window.devicePixelRatio?(this.$refs.homePage.style.height=this.clientHeight-80+"px",console.log(this.clientHeight)):2==window.devicePixelRatio?(this.$refs.homePage.style.height=this.clientHeight-160+"px",console.log(this.clientHeight)):3==window.devicePixelRatio&&(this.$refs.homePage.style.height=this.clientHeight-240+"px",console.log(this.clientHeight))},created:function(){var t=this,e=new FormData;console.log(e);this.$axios.post("/v3/area_list",e,{headers:{"Content-Type":"application/json"}}).then(function(e){console.log(t.items),console.log(e),t.items=e.data.data})}},_={render:function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",[a("Header",{attrs:{title:t.msg}}),t._v(" "),a("div",{ref:"homePage",staticClass:"data_list"},[a("van-row",[a("van-col",{attrs:{span:6,offset:1}},[a("div",{directives:[{name:"show",rawName:"v-show",value:t.ProvinceBtnShow,expression:"ProvinceBtnShow"}],on:{click:t.toggleProvince}},[a("van-icon",{directives:[{name:"show",rawName:"v-show",value:t.CityBtnShow,expression:"CityBtnShow"}],attrs:{name:"success"}}),t._v(t._s(t.ProvinceValue))],1)]),t._v(" "),a("van-col",{attrs:{span:6,offset:1}},[a("div",{directives:[{name:"show",rawName:"v-show",value:t.CityBtnShow,expression:"CityBtnShow"}],on:{click:t.toggleCity}},[a("van-icon",{directives:[{name:"show",rawName:"v-show",value:t.AreaBtnShow,expression:"AreaBtnShow"}],attrs:{name:"success"}}),t._v(t._s(t.CityValue))],1)]),t._v(" "),a("van-col",{attrs:{span:6,offset:1}},[a("div",{directives:[{name:"show",rawName:"v-show",value:t.AreaBtnShow,expression:"AreaBtnShow"}],on:{click:t.toggleArea}},[a("van-icon",{directives:[{name:"show",rawName:"v-show",value:t.chooseStatus,expression:"chooseStatus"}],attrs:{name:"success"}}),t._v(t._s(t.AreaValue))],1)])],1),t._v(" "),a("vue-better-scroll",{directives:[{name:"show",rawName:"v-show",value:t.ProvinceShow,expression:"ProvinceShow"}],ref:"scroll1",staticClass:"wrapper",attrs:{startY:parseInt(t.startY)}},[a("ul",{ref:"page"},t._l(t.items,function(e,s){return a("li",{key:e.id,on:{click:function(e){return t.ProvinceClick(e,s)}}},[t._v(t._s(e.province))])}),0)]),t._v(" "),a("vue-better-scroll",{directives:[{name:"show",rawName:"v-show",value:t.CityShow,expression:"CityShow"}],ref:"scroll2",staticClass:"wrapper",attrs:{startY:parseInt(t.startY)}},[a("ul",{ref:"page"},t._l(t.items[t.provinceIndex].children,function(e,s){return a("li",{key:e.id,on:{click:function(e){return t.CitiesClick(e,s)}}},[t._v(t._s(e.city))])}),0)]),t._v(" "),a("vue-better-scroll",{directives:[{name:"show",rawName:"v-show",value:t.AreaShow,expression:"AreaShow"}],ref:"scroll3",staticClass:"wrapper",attrs:{startY:parseInt(t.startY)}},[a("ul",{ref:"page"},t._l(t.items[t.provinceIndex].children[t.cityIndex].children,function(e,s){return a("li",{on:{click:function(e){return t.AreaClick(e,s)}}},[t._v(t._s(e))])}),0)])],1)],1)},staticRenderFns:[]};var w=a("VU/8")(g,_,!1,function(t){a("8bjl")},"data-v-17fbefab",null).exports,y=a("Txow"),b={render:function(){var t=this.$createElement,e=this._self._c||t;return e("div",[this.hasBmView?this._e():e("div",{ref:"view",staticStyle:{width:"100%",height:"100%"}}),this._v(" "),this._t("default")],2)},staticRenderFns:[]},C={data:function(){return{msg:"选择位置",ak:"efhNFs0eQd5NA9cLUnNeIt4XwK6xvBVW",center:{lng:114.02597366,lat:22.54605355},zoom:16,location:"",keyword:"",address:"",current:0,confirmedAddress:"",jgNameDialog:!1,show:!1,startY:0,scrollToX:0,scrollToY:0,scrollToTime:700,locationList:[],geolocation:null,font:"24PX",map:null,styleJson:{style:"normal"},add:{province:"",city:"",district:"",street:"",streetNumber:""},columns:[{id:1,bTitle:"海关大厦",lTitle:"新安路28号"},{id:2,bTitle:"杭州",lTitle:"杭州市后巷2"},{id:3,bTitle:"杭州",lTitle:"杭州市后巷3"},{id:4,bTitle:"杭州",lTitle:"杭州市后巷4"},{id:5,bTitle:"杭州",lTitle:"杭州市后巷5"},{id:6,bTitle:"杭州",lTitle:"杭州市后巷5"},{id:7,bTitle:"杭州",lTitle:"杭州市后巷5"},{id:8,bTitle:"杭州",lTitle:"杭州市后巷5"},{id:9,bTitle:"杭州",lTitle:"杭州市后巷5"},{id:10,bTitle:"杭州",lTitle:"杭州市后巷5"}],initLocation:!1}},components:{Header:l,BaiduMap:a("VU/8")(y.a,b,!1,null,null,null).exports},methods:{handler:function(t){var e=t.BMap,a=t.map,s=this;s.geolocation=new e.Geolocation,s.geolocation.getCurrentPosition(function(t){if(this.getStatus()==BMAP_STATUS_SUCCESS){var n=new e.Marker(t.point);a.addOverlay(n),a.panTo(t.point),s.center.lng=t.point.lng,s.center.lat=t.point.lat,function(t,s){a.clearOverlays();var n=new e.Point(t,s),o=new e.Marker(n);a.addOverlay(o),a.panTo(n)}(t.point.lng,t.point.lat),console.log(s.center.lng,s.center.lat),s.$jsonp("http://api.map.baidu.com/geocoder/v2/?ak=efhNFs0eQd5NA9cLUnNeIt4XwK6xvBVW&location="+s.center.lat+","+s.center.lng+"&output=json&pois=1").then(function(t){for(var e in s.locationList=t,console.log(s.locationList.result.pois[0].name),s.locationList.result.pois)s.columns[e].bTitle=s.locationList.result.pois[e].name,s.columns[e].lTitle=s.locationList.result.pois[e].addr})}else this.$toast("加载失败，请重新进入页面")})},loadding:function(){console.log(this.center)},chooseClick:function(t){this.current=t,console.log(this.current),this.confirmedAddress=this.columns[this.current].lTitle,sessionStorage.setItem("detailLocation",f()(this.confirmedAddress))},hand:function(){this.$toast({message:"hello"})},getPoint:function(t){},scrollTo:function(){this.$refs.scroll.scrollTo(this.scrollToX,this.scrollToY,this.scrollToTime)},next:function(){this.$router.push("/store")}},mounted:function(){console.log(document.body.offsetHeight),this.$refs.homePage.style.minHeight=document.body.offsetHeight+"px",this.$refs.map.style.height=document.body.offsetHeight-264+"px"},watch:{center:function(){}}},x={render:function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",{ref:"homePage",staticClass:"chooselocation"},[a("Header",{attrs:{title:t.msg}}),t._v(" "),a("van-cell-group",{staticClass:"input_lo input_center"},[a("van-field",{attrs:{"left-icon":"search",placeholder:"请输入您的店铺地址"},model:{value:t.keyword,callback:function(e){t.keyword=e},expression:"keyword"}})],1),t._v(" "),a("div",{ref:"map",staticClass:"map_container"},[a("baidu-map",{ref:"bdmap",staticClass:"map",style:{fontSize:t.font},attrs:{ak:t.ak,center:t.center,zoom:t.zoom,mapStyle:t.styleJson},on:{ready:t.handler,touchend:function(e){return t.getPoint(e)},load:t.loadding}},[a("bm-geolocation",{attrs:{anchor:"BMAP_ANCHOR_BOTTOM_RIGHT",showAddressBar:!0,autoLocation:!0}}),t._v(" "),a("bm-marker",{attrs:{position:{lng:t.center.lng,lat:t.center.lat},dragging:!0,animation:"BMAP_ANIMATION_BOUNCE",icon:{url:"/static/img/x2/icon_lc_xhao@2x.png",size:{width:20,height:20}}}}),t._v(" "),a("bm-local-search",{attrs:{keyword:t.keyword,"auto-viewport":!0,location:t.location}})],1)],1),t._v(" "),a("div",{staticClass:"van-picker"},[a("vue-better-scroll",{ref:"scroll",staticClass:"wrapper",attrs:{startY:parseInt(t.startY)}},[t._l(t.columns,function(e,s){return a("van-row",{staticClass:"column"},[a("van-col",{attrs:{span:22}},[a("div",{on:{touchend:function(e){return t.chooseClick(s)}}},[a("p",[t._v(t._s(e.bTitle))]),t._v(" "),a("p",[t._v(t._s(e.lTitle))])])]),t._v(" "),a("van-col",{attrs:{span:2}},[a("van-icon",{class:{active:s==t.current},attrs:{name:"success"}})],1)],1)}),t._v(" "),a("van-row",{staticClass:"column"},[a("van-col")],1)],2)],1),t._v(" "),a("van-button",{attrs:{size:"large",square:"",type:"info"},on:{click:t.next}},[t._v("确认地址")])],1)},staticRenderFns:[]};var T=a("VU/8")(C,x,!1,function(t){a("LXwj")},"data-v-27f8c700",null).exports,k={data:function(){return{msg:"2/3 资质认证",realName:"",IDnumber:"",isrealName:!1,isIDnumber:!1,file:null,img:"",requireList:["1.照片方向正确，不能颠倒 ","2.照片上的字清晰可见，边框完整，能看见国徽","3.如果是复印件，必须加盖印章","4.没有营业执照，可到工商局办理后申请"]}},components:{Header:l},methods:{onRead:function(t){this.file=t,console.log(this.file.file),console.log(this.file.file.name),console.log(t.file.name),this.$axios({methods:"post",url:"/image_upload",params:{file:"http://pqk40fvkr.bkt.clouddn.com/"+this.file.file.name}}).then(function(t){console.log(t),console.log(t.msg),console.log(t.code),alert("a")})},oversizeMethod:function(){this.$toast("图片大小不能超过2M"),console.log("a")},regexpName:function(){/^[\u4e00-\u9fa5A-Za-z0-9-_]{1,15}$/.test(this.real_name)&&(this.isrealName=!0)},regexpIDnumber:function(){/^[1-9][0-9]{5}([1][9][0-9]{2}|[2][0][0|1][0-9])([0][1-9]|[1][0|1|2])([0][1-9]|[1|2][0-9]|[3][0|1])[0-9]{3}([0-9]|[X])$/.test(this.IDnumber)?this.isIDnumber=!0:this.$toast("输入格式不正确或为空")},next:function(){this.isrealName||this.$toast("姓名不能为空"),this.isIDnumber||this.$toast("身份证号不能为空"),this.isrealName&&this.isIDnumber&&this.$router.push("/classify")}}},S={render:function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",{staticClass:"identification"},[a("Header",{attrs:{title:t.msg}}),t._v(" "),a("div",{staticClass:"part1"},[a("van-row",{staticClass:"store_title"},[a("van-col",{staticClass:"store_title_baseinfo",attrs:{span:6}},[t._v("基本信息")])],1),t._v(" "),a("van-row",{staticClass:"real_name"},[a("van-col",{attrs:{span:8}},[t._v("姓名"),a("i",[t._v("*")])]),t._v(" "),a("van-col",{attrs:{span:16}},[a("input",{directives:[{name:"model",rawName:"v-model",value:t.realName,expression:"realName"}],attrs:{type:"text",placeholder:"请输入真实姓名"},domProps:{value:t.realName},on:{blur:t.regexpName,input:function(e){e.target.composing||(t.realName=e.target.value)}}})])],1),t._v(" "),a("van-row",{staticClass:"id_number"},[a("van-col",{attrs:{span:8}},[t._v("身份证号"),a("i",[t._v("*")])]),t._v(" "),a("van-col",{attrs:{span:16}},[a("input",{directives:[{name:"model",rawName:"v-model",value:t.IDnumber,expression:"IDnumber"}],attrs:{type:"text",placeholder:"请输入身份证号"},domProps:{value:t.IDnumber},on:{blur:t.regexpIDnumber,input:function(e){e.target.composing||(t.IDnumber=e.target.value)}}})])],1),t._v(" "),a("van-row",{staticClass:"id_photo"},[a("van-col",{attrs:{span:24}},[a("div",{staticClass:"id_photo_msg"},[t._v("法定代表人手持身份证照"),a("i",[t._v("*")])])])],1),t._v(" "),a("van-row",{staticClass:"id_photo_upload"},[a("van-col",{attrs:{span:24}},[a("van-uploader",{attrs:{"after-read":t.onRead,oversize:t.oversizeMethod,accept:"image/gif,image/jpeg","max-size":1048576}},[a("van-icon",{attrs:{name:"plus"}}),t._v(" "),a("img",{attrs:{src:t.img,alt:""}}),t._v(" "),a("p",[t._v("点击拍摄/上传证件")])],1)],1)],1),t._v(" "),a("van-row",{staticClass:"id_photo_request"},[a("van-col",{attrs:{span:24}},[t._v("请确保图片完整，五官清晰，身份证上文字可辨识")])],1)],1),t._v(" "),a("div",{staticClass:"part2"},[a("van-row",{staticClass:"store_title"},[a("van-col",{staticClass:"store_title_baseinfo",attrs:{span:6}},[t._v("特许证件")])],1),t._v(" "),a("van-row",{staticClass:"license"},[a("van-col",{attrs:{span:24}},[a("div",{staticClass:"license_photo_msg"},[t._v("营业执照"),a("i",[t._v("*")])])])],1),t._v(" "),a("van-row",{staticClass:"license_uload"},[a("van-col",{attrs:{span:24}},[a("van-uploader",{attrs:{"after-read":t.onRead}},[a("van-icon",{attrs:{name:"plus"}}),t._v(" "),a("p",[t._v("点击拍摄/上传证件")])],1)],1)],1),t._v(" "),a("van-row",{staticClass:"license_photo_request"},[a("van-col",{attrs:{span:24}},t._l(t.requireList,function(e){return a("p",[t._v(t._s(e))])}),0)],1)],1),t._v(" "),a("van-row",{staticClass:"store_btn_next"},[a("van-col",{attrs:{span:22,offset:1}},[a("van-button",{attrs:{type:"info",size:"large"},on:{click:t.next}},[t._v("下一步")])],1)],1)],1)},staticRenderFns:[]};var P=a("VU/8")(k,S,!1,function(t){a("G7iQ")},"data-v-34e68224",null).exports,N=(a("GQaK"),{data:function(){return{msg:"3/3 店铺分类",listLength:9,clientHeight:0,current1:0,current2:0,current3:0,value1:"",value2:"",value3:"",startY:0,scrollToX:0,scrollToY:0,scrollToTime:700,list:[{id:"1",name:"一二三四",parts:[{id:"2",name:"打火机",parts:[{id:"3",name:"打火机"},{id:"4",name:"XXX"},{id:"3",name:"打火机"},{id:"4",name:"XXX"},{id:"3",name:"打火机"},{id:"4",name:"XXX"},{id:"3",name:"打火机"},{id:"4",name:"XXX"},{id:"3",name:"打火机"},{id:"4",name:"XXX"},{id:"3",name:"打火机"},{id:"4",name:"XXX"},{id:"3",name:"打火机"},{id:"4",name:"XXX"},{id:"3",name:"打火机"},{id:"4",name:"XXX"},{id:"3",name:"打火机"},{id:"4",name:"XXX"}]},{id:"5",name:"XXX",parts:[{id:"6",name:"打火机"},{id:"7",name:"XXX"}]}]},{id:"8",name:"加多宝",parts:[{id:"9",name:"吸管"}]},{id:"10",name:"耳机",parts:[{id:"11",name:"吸管2",parts:[{id:"3",name:"打火机"},{id:"4",name:"XXX"}]}]}]}},components:{Header:l},methods:{clickLevel1:function(t,e){this.current1=t,this.current2=0,this.current3=0,this.value1=e.name},clickLevel2:function(t,e){this.current2=t,this.current3=0,this.value2=e.name},clickLevel3:function(t,e){this.current3=t,this.value3=e.name},scrollTo:function(){this.$refs.scroll1.scrollTo(this.scrollToX,this.scrollToY,this.scrollToTime),this.$refs.scroll2.scrollTo(this.scrollToX,this.scrollToY,this.scrollToTime),this.$refs.scroll3.scrollTo(this.scrollToX,this.scrollToY,this.scrollToTime)}},mounted:function(){this.clientHeight=document.body.offsetHeight,console.log(this.clientHeight),this.$refs.btn.$el.style.bottom=0,this.listLength=this.list.length,console.log(this.clientHeight),this.$refs.homePage.style.height=document.body.offsetHeight+"px",this.$refs.page.style.height=document.body.offsetHeight-80+"px"},created:function(){var t=this,e=new FormData;console.log(e);this.$axios.post("/v3/gc_list",e,{headers:{"Content-Type":"application/json"}}).then(function(e){console.log(t.list),console.log(e),t.list=e.data.data})}}),M={render:function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",{ref:"homePage",staticClass:"classify"},[a("Header",{attrs:{title:t.msg}}),t._v(" "),a("div",{ref:"page",staticClass:"classify_level"},[a("van-row",[a("van-col",{attrs:{span:7}},[a("vue-better-scroll",{ref:"scroll1",attrs:{startY:parseInt(t.startY)}},[a("ul",{staticClass:"classify_level1_ul"},t._l(t.list,function(e,s){return s<t.listLength?a("li",{key:e.gc_id,class:{active_level1:s==t.current1},on:{touchend:function(a){return t.clickLevel1(s,e)}}},[a("span",[t._v("\n\t\t\t\t\t\t\t "+t._s(e.gc_name)+"\n\t\t\t\t\t\t ")])]):t._e()}),0)])],1),t._v(" "),a("van-col",{attrs:{span:8}},[a("vue-better-scroll",{ref:"scroll2",attrs:{startY:parseInt(t.startY)}},[a("ul",{staticClass:"classify_level2_ul"},t._l(t.list[t.current1].children,function(e,s){return a("li",{key:e.gc_id,class:{active_level2:s==t.current2},on:{touchend:function(a){return t.clickLevel2(s,e)}}},[t._v("\n\t\t\t\t\t\t\t\t"+t._s(e.gc_name)+"\n\t\t\t\t\t\t\t")])}),0)])],1),t._v(" "),a("van-col",{attrs:{span:9}},[a("vue-better-scroll",{ref:"scroll2",attrs:{startY:parseInt(t.startY)}},[a("ul",{staticClass:"classify_level3_ul"},t._l(t.list[t.current1].children[t.current2].children,function(e,s){return a("li",{class:{active_level3:s==t.current3},on:{touchend:function(a){return t.clickLevel3(s,e)}}},[t._v(t._s(e.gc_name))])}),0)])],1)],1)],1),t._v(" "),a("van-row",{ref:"btn",staticClass:"next_btn"},[a("van-col",{attrs:{span:22,offset:1}},[a("router-link",{attrs:{to:"/checks"}},[a("van-button",{attrs:{type:"info",size:"large"}},[t._v("提交")])],1)],1)],1)],1)},staticRenderFns:[]};var $=a("VU/8")(N,M,!1,function(t){a("vFFi")},"data-v-7d9fd235",null).exports,B={data:function(){return{msg:"审核状态"}},components:{Header:l}},A={render:function(){var t=this.$createElement,e=this._self._c||t;return e("div",{staticClass:"checkstate"},[e("Header",{attrs:{title:this.msg}}),this._v(" "),e("van-row",[e("van-col",{staticClass:"clock",attrs:{span:24}},[e("div",[e("van-icon",{attrs:{name:"clock"}})],1)])],1),this._v(" "),e("van-row",[e("van-col",{staticClass:"checkMsg1",attrs:{span:24}},[this._v("\n\t\t\t您的入驻申请已提交审核，请等待管理员审核 \n\t\t")])],1),this._v(" "),e("van-row",[e("van-col",{staticClass:"checkMsg2",attrs:{span:24}},[this._v("\n\t\t\t我们将以短信的方式通知您审核结果~\n\t\t")])],1)],1)},staticRenderFns:[]};var I=a("VU/8")(B,A,!1,function(t){a("Jje9")},"data-v-e333d344",null).exports,X={data:function(){return{msg:"审核状态"}},components:{Header:l}},H={render:function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",{staticClass:"checkstate"},[a("Header",{attrs:{title:t.msg}}),t._v(" "),a("van-row",[a("van-col",{staticClass:"clock",attrs:{span:24}},[a("div",[a("van-icon",{attrs:{name:"fail"}})],1)])],1),t._v(" "),a("van-row",[a("van-col",{staticClass:"checkMsg1",attrs:{span:24}},[t._v("\n\t\t\t审核未通过 \n\t\t")])],1),t._v(" "),a("van-row",[a("van-col",{staticClass:"checkMsg2",attrs:{span:24}},[t._v("\n\t\t\t您提交的入驻申请未通过审核  \n\t\t")])],1),t._v(" "),a("van-row",[a("van-col",{staticClass:"checkMsg2",attrs:{span:24}},[t._v("\n\t\t\t未通过原因：\n\t\t")])],1),t._v(" "),a("van-row",[a("van-col",{staticClass:"checkMsg2",attrs:{span:24}},[a("ul",[a("li",[t._v("（1）营业执照信息不够全面")]),t._v(" "),a("li",[t._v("（2）开户银行信息不对")]),t._v(" "),a("li",[t._v("（3）身份信息不符合")])])])],1)],1)},staticRenderFns:[]};var z=a("VU/8")(X,H,!1,function(t){a("SD+f")},"data-v-3e6adba8",null).exports,D={data:function(){return{msg:"入驻申请"}},components:{Header:l},methods:{onRead:function(){}}},R={render:function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",{staticClass:"application"},[a("Header",{attrs:{title:t.msg}}),t._v(" "),a("van-row",{staticClass:"pay_info"},[a("van-col",{attrs:{span:1,offset:4}},[a("van-icon",{attrs:{name:"checked"}})],1),t._v(" "),a("van-col",{attrs:{span:16,offset:2}},[t._v("\n\t\t\t您的审核已通过，请缴费\n\t\t")])],1),t._v(" "),a("van-row",{staticClass:"count"},[a("van-col",{attrs:{span:22,offset:1}},[a("div",[a("p",[t._v("￥1000")]),t._v(" "),a("p",[t._v("应缴纳总金额(元)")])])])],1),t._v(" "),a("van-row",{staticClass:"count_detail"},[a("van-col",{attrs:{span:22,offset:1}},[t._v("\n\t\t\t普通店铺一年服务费：900 元+经营保证金：100 元\n\t\t")])],1),t._v(" "),a("van-row",{staticClass:"transfer_voucher"},[a("van-col",{attrs:{span:22,offset:1}},[t._v("\n\t\t\t您可通过以下方式缴费：\n\t\t")])],1),t._v(" "),a("van-row",{staticClass:"voucher"},[a("van-col",{attrs:{span:10,offset:1}},[t._v("\n\t\t\t上传转账凭证"),a("i",[t._v("*")])]),t._v(" "),a("van-col",{attrs:{span:4,offset:8}},[a("van-uploader",{attrs:{"after-read":t.onRead}},[a("van-icon",{attrs:{name:"plus"}})],1)],1)],1),t._v(" "),a("van-row",{staticClass:"transfer_info"},[a("van-col",{attrs:{span:22,offset:1}},[a("p",[t._v("1.请转账至官方提供的收款银行账户")]),t._v(" "),a("p",[t._v("2.转账后请上传转账凭证，以便我们及时确认收款")])])],1),t._v(" "),a("van-row",{staticClass:"btn"},[a("van-col",{attrs:{span:22,offset:1}},[a("router-link",{attrs:{to:""}},[a("van-button",{attrs:{size:"large",type:"info"}},[t._v("提交")])],1)],1)],1)],1)},staticRenderFns:[]};var Z=a("VU/8")(D,R,!1,function(t){a("8DTz")},"data-v-51b9d38e",null).exports,L={data:function(){return{msg:"审核状态"}},components:{Header:l}},V={render:function(){var t=this.$createElement,e=this._self._c||t;return e("div",{staticClass:"payment_waiting"},[e("Header",{attrs:{title:this.msg}}),this._v(" "),e("van-row",[e("van-col",{staticClass:"clock",attrs:{span:24}},[e("div",[e("van-icon",{attrs:{name:"clock"}})],1)])],1),this._v(" "),e("van-row",[e("van-col",{staticClass:"checkMsg1",attrs:{span:24}},[this._v("\n\t\t\t您已成功提交缴费，请等待管理员缴费审核\n\t\t")])],1),this._v(" "),e("van-row",[e("van-col",{staticClass:"checkMsg2",attrs:{span:24}},[this._v("\n\t\t\t我们将以短信的方式通知您审核结果~\n\t\t")])],1)],1)},staticRenderFns:[]};var F=a("VU/8")(L,V,!1,function(t){a("2dnA")},"data-v-7a428193",null).exports,O={data:function(){return{msg:"审核状态"}},components:{Header:l}},Y={render:function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",{staticClass:"checkstate"},[a("Header",{attrs:{title:t.msg}}),t._v(" "),a("van-row",[a("van-col",{staticClass:"clock",attrs:{span:24}},[a("div")])],1),t._v(" "),a("van-row",[a("van-col",{staticClass:"checkMsg1",attrs:{span:24}},[t._v("\n\t\t\t入驻成功\n\t\t")])],1),t._v(" "),a("van-row",[a("van-col",{staticClass:"checkMsg2",attrs:{span:24}},[t._v("\n\t\t\t您已成功入驻邻邻发\n\t\t")])],1),t._v(" "),a("van-row",[a("van-col",{staticClass:"checkMsg2",attrs:{span:24}},[t._v("\n\t\t\t请前往商家平台进一步完善您的店铺信息\n\t\t")])],1),t._v(" "),a("van-row",{staticClass:"next"},[a("van-col",{attrs:{span:22,offset:1}},[a("van-button",{attrs:{size:"large",type:"info"}},[t._v("进入店铺")])],1)],1)],1)},staticRenderFns:[]};var E=a("VU/8")(O,Y,!1,function(t){a("5pmT")},"data-v-62370537",null).exports,U={render:function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",[a("vue-better-scroll",{ref:"scroll",staticClass:"wrapper",attrs:{startY:parseInt(t.startY)}},[a("ul",{staticClass:"list-content"},t._l(t.list,function(e){return a("li",{staticClass:"list-item"},[t._v(t._s(e))])}),0)])],1)},staticRenderFns:[]};var j=a("VU/8")({data:function(){return{list:[1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18],startY:0,scrollToX:0,scrollToY:0,scrollToTime:700}},mounted:function(){},methods:{scrollTo:function(){this.$refs.scroll.scrollTo(this.scrollToX,this.scrollToY,this.scrollToTime)}}},U,!1,function(t){a("veCa")},"data-v-4d946fed",null).exports;s.a.use(i.a);var J=new i.a({routes:[{name:"enter",path:"/",component:h},{name:"newfile",path:"/newfile",component:j},{path:"/enter",name:"enter",component:h},{path:"/store/",name:"store",component:m,meta:{keepAlive:!0}},{path:"/choosecity",name:"choosecity",component:w},{path:"/chooselo",name:"chooselo",component:T},{path:"/ident",name:"ident",component:P},{path:"/classify",name:"classify",component:$},{path:"/checks",name:"checkstate",component:I},{path:"/checkf",name:"checkStateFailed",component:z},{path:"/application",name:"application",component:Z},{path:"/pwait",name:"pwait",component:F},{path:"/enters",name:"enters",component:E}]}),W=(a("sVYa"),a("mtWM")),q=a.n(W),K=(a("Rf8U"),a("h2tb"),a("mw3O")),G=a.n(K),Q=a("NYxO"),tt=(a("MPzD"),a("9KLQ"),a("ndio"),a("Fd2+")),et=(a("4ml/"),a("dAEq")),at=a.n(et),st=a("7oBh"),nt=a.n(st),ot=a("vhKK"),it=a.n(ot),rt=a("nAkq"),lt=a.n(rt),ct=a("qJdI"),vt=a.n(ct);q.a.defaults.withCredentials=!1,q.a.defaults.baseURL="http://47.111.27.189:2000",q.a.defaults.headers={"Content-Type":"application/x-www-form-urlencoded"},s.a.config.productionTip=!1,s.a.use(Q.a),s.a.use(tt.a),s.a.use(vt.a),s.a.use(at.a,{ak:"efhNFs0eQd5NA9cLUnNeIt4XwK6xvBVW"}),s.a.use(it.a,{AlloyFinger:nt.a}),s.a.use(lt.a),s.a.prototype.$axios=q.a,s.a.prototype.qs=G.a,new s.a({el:"#app",router:J,components:{App:o},template:"<App/>"})},"SD+f":function(t,e){},Txow:function(t,e,a){"use strict";(function(t){var s=a("//Fk"),n=a.n(s),o=a("BO1k"),i=a.n(o),r=a("HpPs"),l=a("S4eb");e.a={name:"bm-map",props:{ak:{type:String},center:{type:[Object,String]},zoom:{type:Number},minZoom:{type:Number},maxZoom:{type:Number},highResolution:{type:Boolean,default:!0},mapClick:{type:Boolean,default:!0},mapType:{type:String},dragging:{type:Boolean,default:!0},scrollWheelZoom:{type:Boolean,default:!1},doubleClickZoom:{type:Boolean,default:!0},keyboard:{type:Boolean,default:!0},inertialDragging:{type:Boolean,default:!0},continuousZoom:{type:Boolean,default:!0},pinchToZoom:{type:Boolean,default:!0},autoResize:{type:Boolean,default:!0},theme:{type:Array},mapStyle:{type:Object}},watch:{center:function(t,e){var a=this.map,s=this.zoom;"String"===Object(l.a)(t)&&t!==e&&a.centerAndZoom(t,s)},"center.lng":function(t,e){var a=this.BMap,s=this.map,n=this.zoom,o=this.center;t!==e&&t>=-180&&t<=180&&s.centerAndZoom(new a.Point(t,o.lat),n)},"center.lat":function(t,e){var a=this.BMap,s=this.map,n=this.zoom,o=this.center;t!==e&&t>=-74&&t<=74&&s.centerAndZoom(new a.Point(o.lng,t),n)},zoom:function(t,e){var a=this.map;t!==e&&t>=3&&t<=19&&a.setZoom(t)},minZoom:function(t){this.map.setMinZoom(t)},maxZoom:function(t){this.map.setMaxZoom(t)},highResolution:function(){this.reset()},mapClick:function(){this.reset()},mapType:function(e){this.map.setMapType(t[e])},dragging:function(t){var e=this.map;t?e.enableDragging():e.disableDragging()},scrollWheelZoom:function(t){var e=this.map;t?e.enableScrollWheelZoom():e.disableScrollWheelZoom()},doubleClickZoom:function(t){var e=this.map;t?e.enableDoubleClickZoom():e.disableDoubleClickZoom()},keyboard:function(t){var e=this.map;t?e.enableKeyboard():e.disableKeyboard()},inertialDragging:function(t){var e=this.map;t?e.enableInertialDragging():e.disableInertialDragging()},continuousZoom:function(t){var e=this.map;t?e.enableContinuousZoom():e.disableContinuousZoom()},pinchToZoom:function(t){var e=this.map;t?e.enablePinchToZoom():e.disablePinchToZoom()},autoResize:function(t){var e=this.map;t?e.enableAutoResize():e.disableAutoResize()},theme:function(t){this.map.setMapStyle({styleJson:t})},"mapStyle.features":{handler:function(t,e){var a=this.map,s=this.mapStyle,n=s.style,o=s.styleJson;a.setMapStyle({styleJson:o,features:t,style:n})},deep:!0},"mapStyle.style":function(t,e){var a=this.map,s=this.mapStyle,n=s.features,o=s.styleJson;a.setMapStyle({styleJson:o,features:n,style:t})},"mapStyle.styleJson":{handler:function(t,e){var a=this.map,s=this.mapStyle,n=s.features,o=s.style;a.setMapStyle({styleJson:t,features:n,style:o})},deep:!0},mapStyle:function(t){var e=this.map;!this.theme&&e.setMapStyle(t)}},methods:{setMapOptions:function(){var e=this.map,a=this.minZoom,s=this.maxZoom,n=this.mapType,o=this.dragging,i=this.scrollWheelZoom,r=this.doubleClickZoom,l=this.keyboard,c=this.inertialDragging,v=this.continuousZoom,h=this.pinchToZoom,p=this.autoResize;a&&e.setMinZoom(a),s&&e.setMaxZoom(s),n&&e.setMapType(t[n]),o?e.enableDragging():e.disableDragging(),i?e.enableScrollWheelZoom():e.disableScrollWheelZoom(),r?e.enableDoubleClickZoom():e.disableDoubleClickZoom(),l?e.enableKeyboard():e.disableKeyboard(),c?e.enableInertialDragging():e.disableInertialDragging(),v?e.enableContinuousZoom():e.disableContinuousZoom(),h?e.enablePinchToZoom():e.disablePinchToZoom(),p?e.enableAutoResize():e.disableAutoResize()},init:function(t){if(!this.map){var e=this.$refs.view,a=!0,s=!1,n=void 0;try{for(var o,l=i()(this.$slots.default||[]);!(a=(o=l.next()).done);a=!0){var c=o.value;c.componentOptions&&"bm-view"===c.componentOptions.tag&&(this.hasBmView=!0,e=c.elm)}}catch(t){s=!0,n=t}finally{try{!a&&l.return&&l.return()}finally{if(s)throw n}}var v=new t.Map(e,{enableHighResolution:this.highResolution,enableMapClick:this.mapClick});this.map=v;var h=this.setMapOptions,p=this.zoom,u=this.getCenterPoint,m=this.theme,d=this.mapStyle;m?v.setMapStyle({styleJson:m}):v.setMapStyle(d),h(),r.a.call(this,v),v.reset(),v.centerAndZoom(u(),p),this.$emit("ready",{BMap:t,map:v})}},getCenterPoint:function(){var t=this.center,e=this.BMap;switch(Object(l.a)(t)){case"String":return t;case"Object":return new e.Point(t.lng,t.lat);default:return new e.Point}},initMap:function(t){this.BMap=t,this.init(t)},getMapScript:function(){if(t.BMap)return t.BMap._preloader?t.BMap._preloader:n.a.resolve(t.BMap);var e=this.ak||this._BMap().ak;return t.BMap={},t.BMap._preloader=new n.a(function(a,s){t._initBaiduMap=function(){a(t.BMap),t.document.body.removeChild(n),t.BMap._preloader=null,t._initBaiduMap=null};var n=document.createElement("script");t.document.body.appendChild(n),n.src="https://api.map.baidu.com/api?v=2.0&ak="+e+"&callback=_initBaiduMap"}),t.BMap._preloader},reset:function(){var t=this.getMapScript,e=this.initMap;t().then(e)}},mounted:function(){this.reset()},data:function(){return{hasBmView:!1}}}}).call(e,a("DuR2"))},hPlS:function(t,e){},ndio:function(t,e){},"r/2J":function(t,e){},tp2z:function(t,e){},vFFi:function(t,e){},veCa:function(t,e){}},["NHnr"]);
//# sourceMappingURL=app.cec590f46b6bbc2f4d7a.js.map