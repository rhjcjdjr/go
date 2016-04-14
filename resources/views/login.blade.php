@extends("layout/always")


@push("title", "Вход")
@push("app-title", "Вход")


@push("head-css")
	<link rel="stylesheet" type="text/css" href="/css/bootstrap/bootstrap-social.css">
@endpush


@push("bottom-js")
<script type="text/javascript">
	$(function() {
		$('#login-btn-facebook').popover({
			title: 'Только через Вконтакте',
			content: 'Facebook аккаунт в России только у выпендрежников, но я уверен - ты ошибся кнопкой, приятель.',
			animation: false,
			placement: 'bottom',
			html: true,
			trigger: 'focus manual',
		});
	});
</script>
@endpush

@section("content")
<div class="row">
	<div class="col-md-6" style="margin:0 auto; float:none; margin-top:5px;">
		<button id="login-btn-facebook" class="btn btn-block btn-social btn-facebook">
    		<span class="fa fa-facebook"></span>Войти с помощью Facebook
  		</button>
	</div>
	<div class="col-md-6" style="margin:0 auto; float:none; margin-top:8px;">
		<a href="{{ $vk }}" class="btn btn-block btn-social btn-vk">
    		<span class="fa fa-vk"></span>Войти с помощью Вконтакте
  		</a>
	</div>

<div id="qq" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Внимание:</h4>
      </div>
      <div class="modal-body">
        <p>Сори. Доступа нет!</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Пам пам</button>
        <button type="button" class="btn btn-primary">Тык</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

</div>
@endsection

@push("bottom-js")
<script type="text/javascript">
	@if (isset($qq))
	$(function() {
		$('#qq').modal();
	});
	@endif
</script>
@endpush

