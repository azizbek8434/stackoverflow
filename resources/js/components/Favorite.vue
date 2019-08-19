<template>
  <a title="Click to mark favorite question (Click agan to undo)" :class="classes">
    <i class="fas fa-star fa-2x"></i>
    <span class="favorites-count">{{ count }}</span>
  </a>
</template>
<script>
export default {
  props: ["question"],
  data() {
    return {
      isFavorited: this.question.is_favorited,
      count: this.question.favorite_count,
      signedIn: true
    };
  },
  computed: {
    classes() {
      return [
        "favorite",
        "mt-2",
        !this.signedIn
          ? "off"
          : () => {
              if (this.isFavorited) return "favorited";
              else return "";
            }
      ];
    }
  }
};
</script>