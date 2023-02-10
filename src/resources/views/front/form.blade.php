@if ($errors ?? false)
    @foreach ($errors->all() as $error)
        <div style="color:red">
            {{ $error }}<br>
        </div>
    @endforeach

    <br>
@endif
<form id="firewall-form"
      action="/debug/firewall"
      method="POST">
    @csrf

    <label for="input1">Label</label>

    <input id="input1"
           name="input1"
           type="text">

    @if ($TwillFirewall['enabled'])
        <input id="g-recaptcha-response"
               name="g-recaptcha-response"
               type="hidden">

        <button type="button"
                onclick="return onSubmitClick();">
            Submit
        </button>
    @else
        <button type="button">Submit</button>
    @endif

    <br>

    <div>Site key: {{ $TwillFirewall['keys']['username'] }}</div>
</form>

@if ($TwillFirewall['enabled'])
    <script src="{{ $TwillFirewall['asset'] }}"></script>

    <script>
        console.log('HTTP Basic Auth 3 loaded');

        function onSubmitClick(e) {
            grecaptcha.ready(function() {
                grecaptcha.execute('{{ $TwillFirewall['keys']['username'] }}', {
                    action: 'submit'
                }).then(function(token) {
                    document.getElementById("g-recaptcha-response").value = token;
                    document.getElementById("firewall-form").submit();
                });
            });
        }
    </script>
@endif
