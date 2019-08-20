<template>
  <div class="d-flex flex-column vote-controls">
    <a :title="title('up')" :class="classes">
      <i class="fas fa-caret-up fa-3x"></i>
    </a>
    <span class="vote-count">{{ count }}</span>
    <a :title="title('down')" :class="classes">
      <i class="fas fa-caret-down fa-3x"></i>
    </a>
    <favorite-component v-if="name ==='question'" :question="model"></favorite-component>
    <accept-component :answer="model" v-else></accept-component>
  </div>
</template>
<script>
import AcceptComponent from "./Accept";
import FavoriteComponent from "./Favorite";

export default {
  props: ["name", "model"],
  computed: {
    classes() {
      return this.signedIn ? "" : "off";
    }
  },
  components: {
    FavoriteComponent,
    AcceptComponent
  },
  data() {
    return {
      count: this.model.votes_count
    };
  },
  created() {
    console.log(this.name);
  },
  methods: {
    title(voteType) {
      let titles = {
        up: `This ${this.name} is useful`,
        down: `This ${this.name} is not useful`
      };
      return titles[voteType];
    }
  }
};
</script>