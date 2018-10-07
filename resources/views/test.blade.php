@extends('layouts.app')

@section('content')
    <div class="panel panel-primary" id="chat-box">
        @foreach ($messages as $message)
            <div class="panel-heading">
                <i class="fa fa-user" aria-hidden="true"></i> 
                {{ $message->user_id }}
            </div>
            <div class="box-chat">
                <div class="user">
                    <span class="message">{{ $message->message }}</span>
                    <br>
                    <span class="author-message"><b>Role</b></span>
                </div>
            </div>
        @endforeach
        <div class="panel-footer clearfix">
            <form method="post" id="form-message" action="/message">
            <div class="form-group">
                <div class="input-group">

                    <input type="text" class="form-control" id = "message-content">
                    
                    <input type="hidden" value="{{Auth::id()}}">

                    <div class="input-group-addon">
                        <button type="submit" id="btn-send">SEND</button>
                    </div>
                </div>
            </div>
            </form>

        </div>
    </div>
@endsection

@section('script')
    <script src="{{asset('pusher-js/dist/web/pusher.min.js)')}}"</script>
    <script src="{{asset('js/app.js')}}"></script>
    <script src="{{asset('js/chat.js')}}"></script>

    <script type="text/javascript">
        var chat = new chat();
        chat.init();
    </script>
@endsection