<answer-component :answer="{{ $answer }}" inline-template>
<div class="media post">
    <vote-component :model="{{ $answer }}" name="answer"></vote-component>
    <div class="media-body">
        <form v-if="editing" @submit.prevent="update">
            <div class="form-group">
                <textarea  class="form-control" v-model="body" rows="10" required></textarea>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-outline-primary" :disabled="isInvalid">Update</button>
                <button type="button" class="btn btn-outline-default" @click="cancel">Cancel</button>
            </div>
        </form>
        <div v-else>
            <div v-html="bodyHtml"></div>
        <div class="row d-flex">
            <div class="col-4 justify-content-center align-self-end">
                <div class="ml-auto">
                    @can('update', $answer)
                    <a @click.prevent="edit"
                        class="btn btn-outline-info btn-sm">Edit</a>
                    @endcan
                    @can('delete', $answer)
                        <button @click="destroy" class="btn btn-outline-danger btn-sm">Delete</button>
                    @endcan
                </div>
            </div>
            <div class="col-4"></div>
            <div class="col-4 justify-content-center align-self-end">
                <user-info :model="{{ $answer }}" label="Answered"></user-info>
            </div>
        </div>
       </div>
    </div>
</div>
</answer-component>