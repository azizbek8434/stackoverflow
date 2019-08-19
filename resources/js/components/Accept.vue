<template>
  <div>
    <a
      title="Mark this answer as best answer"
      :class="classes"
      v-if="canAccept"
      @click.prevent="create"
    >
      <i class="fas fa-check fa-2x"></i>
    </a>
    <a
      title="The question owner accepted this answer as best answer"
      :class="classes"
      v-if="accepted"
    >
      <i class="fas fa-check fa-2x"></i>
    </a>
  </div>
</template>
<script>
export default {
  props: ["answer"],
  data() {
    return {
      isBest: this.answer.is_best,
      id: this.answer.id
    };
  },
  computed: {
    canAccept() {
      return this.authorize("accept", this.answer);
    },
    accepted() {
      return !this.canAccept && this.isBest;
    },
    classes() {
      return ["mt-2", this.isBest ? "vote-accepted" : "vote-accept"];
    }
  },
  methods: {
    create() {
      axios.post(`/answers/${this.id}/accept`).then(res => {
        this.$toast.success(res.data.message, "Success", {
          timeout: 3000,
          position: "bottomLeft"
        });
        this.isBest = true;
      });
    }
  }
};
</script>