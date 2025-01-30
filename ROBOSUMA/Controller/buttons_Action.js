function tabcli() {
    $('#modal-loading').modal('toggle')
    setTimeout(() => {
        $('#modal-loading').modal('hide')
        $('#modal-vista').modal('toggle');
  }, 4000);
}

function start() {
    $('#modal-loading').modal('toggle');
    setTimeout(() => {
        $('#modal-loading').modal('hide');
        $('#star1').css('display','block');
        $('#star3').css('display','none');
  }, 4000);
}

function mid() {
    $('#modal-loading').modal('toggle');
    setTimeout(() => {
        $('#modal-loading').modal('hide');
        $('#star2').css('display','block');
        $('#star1').css('display','none');
  }, 4000);
}

function mid_Again() {
    $('#modal-loading').modal('toggle');
    setTimeout(() => {
        $('#modal-loading').modal('hide');
        $('#star1').css('display','block');
        $('#star2').css('display','none');
  }, 4000);
}

function end() {
    $('#modal-loading').modal('toggle');
    setTimeout(() => {
        $('#modal-loading').modal('hide');
        $('#star3').css('display','block');
        $('#star2').css('display','none');
  }, 4000);
}

