$('.login-me-as-user').on('submit',function (e) {
    e.preventDefault();
    let login_message=$('.login-message');
    let submit_btn=$('.btn-to-rotate')
    $.ajax({
        url:'http://localhost/LF/api/login',
        method:'POST',
        dataType: 'json',
        data: $('.login-me-as-user').serialize(),
        beforeSend:function(){
            submit_btn.prop('disabled', true);
            submit_btn.text('');
            submit_btn.append(' <i class="fas fa-circle-notch fa-spin"></i>');
        },
        success:function (response) {
            login_message.text(response.success);
            console.log(response.success);
            submit_btn.prop('disabled', true);
            submit_btn.text('');
            submit_btn.text('Redirecting');
            submit_btn.append(' <i class="fas fa-circle-notch fa-spin"></i>');

            setTimeout(function(){
               // alert(sessionStorage.getItem('redirectionURL'));
                if(sessionStorage.getItem('redirectionURL')===null){
                    window.location.replace('search-lost-things');
                }
                else
                    //alert(sessionStorage.getItem('redirectionURL'));
                window.location.replace(sessionStorage.getItem('redirectionURL'));
            }, 1000);
        },
        error:function (response) {
            submit_btn.prop('disabled', false);
            submit_btn.text('');
            submit_btn.append('Sign In');
            login_message.addClass('text-danger');
            login_message.text(response.responseJSON.error);
            console.log(response.responseJSON.error)
            // console.log(response.responseJSON.error);
        }
    })
})
//register
$('.register-form').on('submit',function (e) {

    e.preventDefault();
    let login_message=$('.error-message');
    let submit_btn=$('.btn-to-rotate');
    if($('#first-pass').val()===$('#confirm-pass').val())
        $.ajax({
            url:'http://localhost/LF/api/register',
            method:'POST',
            dataType: 'json',
            data: $('.register-form').serialize(),
            beforeSend:function(){
                submit_btn.prop('disabled', true);
                submit_btn.text('');
                submit_btn.append(' <i class="fas fa-circle-notch fa-spin"></i>');
            },
            success:function (response) {
                login_message.text(response.success);
                login_message.addClass('text-success');
                console.log(response.success);
                submit_btn.prop('disabled', true);
                submit_btn.text('');
                submit_btn.text('creating user ');
                submit_btn.append(' <i class="fas fa-circle-notch fa-spin"></i>');

                setTimeout(function(){
                    window.location.replace('login-user');
                }, 1000);
            },
            error:function (response) {
                submit_btn.prop('disabled', false);
                submit_btn.text('');
                submit_btn.append('Sign up now');
                login_message.addClass('text-danger');
                login_message.text(response.responseJSON.error);
                console.log(response.responseJSON.error)
                // console.log(response.responseJSON.error);
            }
        })
    else {
        login_message.text('Password does not match');
        login_message.addClass('text-danger');
    }
})
// $(function(){
//     $('.pcoded-inner-navbar').load('nav');
// });

$('#type').on('change',function () {

    if($('#type').val()==='cnic'){
        $('#label-to-be-changed').text('Your CNIC number');
        hide_fast('cnio','emo','cno');
    }
    else if($('#type').val()==='contact'){
        $('#label-to-be-changed').text('Your contact number');
        hide_fast('cno','emo','cnio');
    }
    else if($('#type').val()==='email'){
        $('#label-to-be-changed').text('Your Email address');
        hide_fast('emo','cnio','cno');
    }

})
function hide_fast(one,two,three) {
        $('.'+one).show();
        $('.'+two).hide();
        $('.'+three).hide();
}
