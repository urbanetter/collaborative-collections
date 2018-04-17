require('../css/app.scss');

require('bootstrap');

import Sortable from 'sortablejs/Sortable';

let changes = [];

function fancyRenderFunction(data) {
  data.forEach(function(collection) {
    let div = $('<div class="card-deck collection">');
    collection.forEach(function (item) {
      let card = $('<div class="card">');
      $('<div class="card-body">')
        .append('<h5 class="card-title">' + item.title + '</h5>')
        .append('<p class="card-text">' + item.lead + '</p>')
        .appendTo(card);
      div.append(card);
    });
    $('#content').append(div);
    Sortable.create(div.get(0), {
      onUpdate: function (event) {
        changes.push({
          type: 'reorder',
          from: event.oldIndex,
          to: event.newIndex
        });
        updateChangeLog();
      }
    });
  })
}

function updateChangeLog() {
  $('#status').text(changes.length + ' changes ready to publish.');
}


$(document).ready(function () {
  $.get('/data', function(data) {
    fancyRenderFunction(data);
  })

});
