<template>
	<div class="uploadImg">
		<van-uploader :after-read="onRead" ref="storeFront">
			  <van-icon :name="imgPath" :class="{active:storeFrontPhoto == true}"/>
		</van-uploader>
	</div>
</template>

<script>
	export default {
		data(){
			return {
				storeFrontPhoto:false,
				imgPath:"plus",
			}
		},
		props:[],
		methods:{
			onRead(file){
				console.log(file);
				console.log(file.file);
				if(file.file.size>=1048576){
					this.storeFrontPhoto = false;
					this.$toast("图片大小不能超过2M");
					this.imgPath = "plus";
				}else{
					var formData = new FormData();
					formData.append("file",file.file);
					let config = {
						headers: {"Content-Type":"multipart/form-data"}
					}
					// this.$axios.post("upload.php",formData,config).then((data)=>{
					// this.imgPath1="http://127.0.0.1:80/"+data.data.path;
					// this.storeFrontPhoto = true;
					// })
					this.$axios.post("/v3/image_upload",formData,config).then((data)=>{
						this.storeFrontPhoto = true;
						this.imgPath1="http://pqk40fvkr.bkt.clouddn.com/"+data.data.data;
					})
				}
					
			}
		}
		
	}
</script>

<style scoped lang="stylus">
	.van-uploader
		width: 120px
		height: 120px
		margin:(170px/2)-(120px/2)  0
		text-align:center
		uploadBorder()
		i
			line-height: 120px
			font-size:25PX
			color:#F0F0F0
		i.active
			width: 118px
			height: 118px
			border-radius:12px
			margin: 1px;
			overflow:hidden
			img
				height: 118px
				width: auto
</style>
