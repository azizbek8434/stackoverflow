<script>
export default {
  props: ["answer"],
  data() {
    return {
      editing: false,
      id: this.answer.id,
      body: this.answer.body,
      bodyHtml: this.answer.body_html,
      questionId: this.answer.question_id,
      beforeEditCache: null
    };
  },
  methods: {
    edit() {
      this.beforeEditCache = this.body;
      this.editing = true;
    },
    cancel() {
      this.body = this.beforeEditCache;
      this.editing = false;
    },
    update() {
      axios
        .patch(`/questions/${this.questionId}/answers/${this.id}`, {
          body: this.body
        })
        .then(response => {
          this.bodyHtml = response.data.body_html;
          this.editing = false;
        })
        .catch(errors => {
          alert(errors.response.data.message);
        });
    }
  },
  computed: {
    isInvalid() {
      return this.body.length < 10;
    }
  }
};
</script>