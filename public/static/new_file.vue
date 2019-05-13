<template>
    <div>
			  <vue-better-scroll class="wrapper"
			                     ref="scroll"
													:startY="parseInt(startY)">
			    <ul class="list-content">
			      <li class="list-item"
			          v-for="item in list">{{item}}</li>
			    </ul>
			  </vue-better-scroll>
		</div>
</template>

<script>
	import BScroll from 'better-scroll'
export default {
			data() {
				return {
					list:[1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18],
        // 这个配置用于做上拉加载功能，默认为 false。当设置为 true 或者是一个 Object 的时候，可以开启上拉加载，可以配置离底部距离阈值（threshold）来决定开始加载的时机
    
        startY: 0, // 纵轴方向初始化位置
        scrollToX: 0,
        scrollToY: 0,
        scrollToTime: 700,
					
				}
			},
  mounted(){
		
		
      },
			methods:{
			 scrollTo() {
							this.$refs.scroll.scrollTo(this.scrollToX, this.scrollToY, this.scrollToTime)
						}
      
    }
  }
</script>

<style scoped lang="stylus">
.position-box
  position: fixed;
  top: 40px;
  left: 0;
  right: 0;
  bottom: 0;
	.wrapper
		width: 350px
		height: 400px
		overflow:hidden
		ul
			width: 350px
			height: 1000px
			li
				width: 350px
				height: 50px
				line-height: 50px
				font-size:17PX
			li.active
				color:cblue
</style>