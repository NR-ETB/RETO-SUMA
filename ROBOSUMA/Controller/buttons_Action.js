function mid() {
  $('#modal-loading').modal('toggle');
  setTimeout(() => {
      $('#modal-loading').modal('hide');
      location.href='View/mid.php';
}, 4000);
}

function mid_Again() {
    $('#modal-loading').modal('toggle');
    setTimeout(() => {
        $('#modal-loading').modal('hide');
        location.href='./mid.php';
  }, 4000);
}

function next_End() {
  $('#modal-loading').modal('toggle');
  setTimeout(() => {
      $('#modal-loading').modal('hide');
      location.href='./end.php';
}, 4000);
}

function end() {
    $('#modal-loading').modal('toggle');
    setTimeout(() => {
        $('#modal-loading').modal('hide');
        location.href='../index.php';
  }, 4000);
}
