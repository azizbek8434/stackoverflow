<template>
  <div>
    <a title="Mark this answer as best answer" :class="classes" v-if="canAccept">
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
      isBest: this.answer.is_best
    };
  },
  computed: {
    canAccept() {
      return true;
    },
    accepted() {
      return !this.canAccept && this.isBest;
    },
    classes() {
      return ["mt-2", this.isBest ? "vote-accepted" : "vote-accept"];
    }
  }
};
</script>