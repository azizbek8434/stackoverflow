@if(session('success'))
<div class="alert alert-success alert-dismissible fade show m-2" role="alert">
  <strong>Success!&nbsp;</strong>{{ session('success') }}.
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
</div>
@endif