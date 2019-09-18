'use strict';

var $checkbox = document.getElementsByClassName('show_completed');

if ($checkbox.length) {
  $checkbox[0].addEventListener('change', function (event) {
    var is_checked = +event.target.checked;

    var searchParams = new URLSearchParams(window.location.search);
    searchParams.set('show_completed', is_checked);

    window.location = '/index.php?' + searchParams.toString();
  });
}

var getTaskId = function(evt) {
  var task_id = evt.target.value;

  var searchParams = new URLSearchParams(window.location.search);
  searchParams.set('task_id', task_id);

  window.location = '/index.php?' + searchParams.toString();
};

var checkboxInputTasks = document.querySelectorAll('.tasks .checkbox__input');
checkboxInputTasks.forEach(function(item) {
  item.addEventListener('change', getTaskId);
});

flatpickr('#date', {
  enableTime: false,
  dateFormat: "Y-m-d",
  locale: "ru"
});
