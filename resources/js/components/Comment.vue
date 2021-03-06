<template>
    <div class="comment-list" :class="isBest ? 'alert alert-primary' : ''">

        <button
                class="float-right"
                :class="classes"
                @click="markBest()"
                v-text="text"
                v-show="authorize('updateThread', comment.thread)">
        </button>

        <div class="single-comment justify-content-between d-flex">
            <div class="user justify-content-between d-flex">
                <div class="thumb">
                <img width="30" height="30" :src="data.user.avatarPath" :alt="data.user.username">
            </div>
                <div class="desc">
                    <h5><a :href="'/profile/'+ data.user.username">{{data.user.username}}</a></h5>
                    <p class="date">{{createdAt(this.data.created_at)}}</p>
                    <p class="comment" v-if="! editing" v-html="body"> </p>
                    <div id="edit-comment-container" v-else>
                        <textarea class="form-control" v-model="body"></textarea><br/>
                        <p class="float-right">
                            <button class="genric-btn primary-border small" @click="update()">Update</button>
                            <button class="genric-btn danger-border small" @click="editing = false">Cancel</button>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="float-right">

            <div v-if="signedIn">
                <div style="display:inline" v-if="authorize('updateComment', comment)">
                    <button class="genric-btn primary-border small" @click.prevent="editing=true" v-if="!editing">
                        <i class="fa fa-edit"></i>
                    </button>

                    <button class="genric-btn danger-border small" @click.prevent="destroy()">
                        <i class="fa fa-trash"></i>
                    </button>
                </div>

                <like-comment :data="data"></like-comment>
            </div>
        </div>

    </div>
</template>


<script>
    import moment from 'moment';
    import LikeComment from './LikeComment';

    export default {

        components: {
            'like-comment': LikeComment
        },

        props: ['data'],

        data(){
            return {
                editing: false,
                body: this.data.body,
                oldBody: '',
                isBest: this.data.isBest,
                comment: this.data
            }
        },

        created(){
            this.isBest = (this.data.id == this.data.thread.best_comment_id);

            Fire.$on('best-selected', (id) => {
                this.isBest = (id == this.data.id);
            });
        },

        computed: {

            text(){
                return this.isBest ? 'Best Comment' : 'Mark Best Comment ?';
            },

            classes(){
                return ['btn', this.isBest ? 'btn-danger' : 'btn-secondary'];
            }
        },

        methods:{

            createdAt($time){
                return moment($time).fromNow();
            },

            update(){
                // old body will work if user has update his comment once
                // and he want to update it again but he write a spam comment
                // so we will rewind the body to be the old body not the main body
                // which came from [this.data.body]
                // this.oldBody = this.body;

                if(this.body == ''){

                    this.$toaster.warning("Comment Cannot Be Empty !");

                }else if(this.body == this.data.body){

                    this.editing = false;

                }else{
                    this.persist();
                }
            },

            persist(){
                axios.patch("/comments/" + this.data.id, {body: this.body})
                    .then(response => {
                        this.editing = false;
                        this.$toaster.success("Your Comment Has Updated");
                    })
                    .catch(error => {
                        this.$toaster.error(error.response.data.errors.body[0]);
                        this.body = this.data.body;
                        this.editing = false;
                    });

            },

            destroy(){
                // $(this.$el).fadeOut(200);
                this.$emit("deleted", this.data.id);
                this.$toaster.success("Your Comment Has Been Deleted");
                axios.delete(`/comments/${this.data.id}`);
            },

            markBest(){
                ! this.isBest ? this.storeBestComment() : this.removeBestComment();
            },

            storeBestComment(){
                this.$toaster.success("You Marked The Best Comment");
                axios.post(`/comments/${this.data.id}/best`);
                Fire.$emit('best-selected', this.data.id);
            },

            removeBestComment(){
                this.isBest = false;
                this.$toaster.warning("You Removed The Best Comment");
                axios.delete(`/comments/${this.data.id}/best`);
            }
        }

    }
</script>

<style>
    #edit-comment-container{
        min-width: 300px;
        max-width: 600px
    }
</style>
