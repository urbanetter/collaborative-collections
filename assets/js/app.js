require('../css/app.scss');

require('bootstrap');

import Sortable from 'sortablejs/Sortable';

let changes = [];
window.changes = changes;

let initialState = [];

function fancyRenderFunction(data) {
    $('#content').text('');
  data.forEach(function(collection) {
    let div = $('<div class="card-deck collection" id="' + collection.id + '">');
    collection.items.forEach(function (item) {
      let card = $('<div class="card" id="' + item.id + '">');
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
          collection: collection.id,
          item: $(event.item).attr('id'),
          position: event.newIndex
        });
        updateChangeLog();
      }
    });
  })
}

window.applyChange = function applyChange(change) {
  if (change.type === 'reorder') {
    $('#' + change.item).insertAfter($('#' + change.collection + ' div.card').eq(change.to));
  }
}

function updateChangeLog() {
  $('#status').text(changes.length + ' changes ready to publish.');
}


$(document).ready(function () {
  $.get('/data', function(data) {
    fancyRenderFunction(data);
    initialState = data;
  });

  $('#publish').on('click', function (event) {
      event.preventDefault();
      if (!changes.length) return;

      console.log(changes);

      $.post('/publish', JSON.stringify(changes), function (data) {
          initialState = data;
          fancyRenderFunction(data);
      }, 'json')
  })

});
