<template>
    <div id="uploadImage">
        <div class="image-upload-container" @drop.prevent="drop($event)" @dragleave="dragLeave()" @dragover.prevent="dragOver()">
            <h5 v-show="dragDrop.drag.visible" class="drop-text"> {{ dragDrop.drag.message }}</h5>
            <h5 v-show="dragDrop.drop.visible" class="drop-text"> {{ dragDrop.drop.message }}</h5>
            <!-- <div class="image" v-for="image in images">
                <img src="" alt="">

            </div> -->
            <div v-html="image" v-for="image in images">
            </div>
            <input type="file" name="designFiles[]">
        </div>
    </div>
</template>

<script>
    export default {
        data(){
            return {
                imageCount: 0,
                images: [],
                dragDrop: {
                    drag:{
                        visible: true,
                        message: 'Drag Images Here...'
                    },
                    drop:{
                        visible: false,
                        message: 'Drop Images Here...'
                    },
                }
            }
        },
        mounted() {
            console.log('Component mounted.')
        },
        components: {

        },
        methods:{
            dragOver(){
                this.dragDrop.drag.visible = false;
                this.dragDrop.drop.visible = true;
            },
            dragLeave(){
                this.dragDrop.drag.visible = true;
                this.dragDrop.drop.visible = false;
            },
            drop(event){
                var scope = this;
                var counter = scope.imageCount;
                var input = document.querySelector('input[name="designFiles[]"]');
                if(input.files){
                    input.files = $.merge( $.merge([], input.files),event.dataTransfer.files);
                }else{
                    input.files = event.dataTransfer.files;
                }
                
            },
            /*readURL(input, image) {
                if (input.files && input.files[0]) {
                    var reader = new FileReader();

                    reader.onload = function(e) {
                        image.setAttribute('src', e.target.result);
                    }
                    reader.readAsDataURL(input.file);
                }
            }*/
        }
    }
</script>
