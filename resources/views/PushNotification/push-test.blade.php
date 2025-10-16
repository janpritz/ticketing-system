@extends('layouts.app')

@section('title', 'Push Test')

@section('content')
    <div class="card">
        <div class="card-header">
            <button onclick="askForPermission()" class="btn btn-success">Enable Notification</button>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <label for='title'>{{ __('title') }}</label>
                    <input type='text' class='form-control' id='title' name='title'>
                </div>
                <div class="col-md-3">
                    <label for='body'>{{ __('body') }}</label>
                    <input type='text' class='form-control' id='body' name='body'>
                </div>
                <div class="col-md-3">
                    <label for='idOfProduct'>{{ __('ID Of Product') }}</label>
                    <input type='text' class='form-control' id='idOfProduct' name='idOfProduct'>
                </div>
                <div class="col-md-3">
                    <input type="button" value="{{ 'Send Notification' }}" onclick="sendNotification()" class="btn btn-info" />
                    <p>Please Enable Push notification before sending</p>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        navigator.serviceWorker.register('/sw.js', {
            scope: '/'
        });

        function askForPermission() {
            Notification.requestPermission().then((permission) => {
                if (permission === 'granted') {
                    // get service worker
                    navigator.serviceWorker.ready.then((sw) => {
                        // subscribe
                        sw.pushManager.subscribe({
                            userVisibleOnly: true,
                            applicationServerKey: '{{ env('PUBLIC_KEY') }}'
                        }).then((subscription) => {
                            console.log(JSON.stringify(subscription));
                            saveSub(JSON.stringify(subscription));
                        });
                    });
                }
            });
        }

        function saveSub(sub) {
            $.ajax({
                type: 'post',
                url: '{{ URL('save-push-notification-sub') }}',
                data: {
                    '_token': "{{ csrf_token() }}",
                    'sub': sub
                },
                success: function(data) {
                    console.log(data);
                }
            });
        }


        function sendNotification() {
            $.ajax({
                type: 'post',
                url: '{{ URL('send-push-notification') }}',
                data: {
                    '_token': "{{ csrf_token() }}",
                    'title': $("#title").val(),
                    'body': $("#body").val(),
                    'idOfProduct': $("#idOfProduct").val(),
                },
                success: function(data) {
                    alert('send Successfull');
                    console.log(data);
                }
            });
        }
    </script>
@endsection