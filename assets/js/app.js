require('../css/app.scss');

require('bootstrap');

import Sortable from 'sortablejs/Sortable';

let changes = [];
window.changes = changes;

let initialState = {};

let username = '';

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
      console.log("$('#'" + change.item + ").insertAfter($('#" + change.collection + " div.card').eq(" + change.position + "));");
      $('#' + change.item).insertAfter($('#' + change.collection + ' div.card').eq(change.position));
  }
}

function updateChangeLog() {
  $('#status').text(changes.length + ' changes ready to publish.');
}

function checkForUpdates() {
    $.get('/version', function(version) {
        if (version.version > initialState.version.version) {
            $('#version').text('New content from ' + version.user + ', click to apply');
        } else {
            $('#version').text('Published version from ' + initialState.version.user);
        }
    });
}


$(document).ready(function () {
  $.get('/data', function(data) {
    fancyRenderFunction(data.collections);
    initialState = data;
  });

  setInterval(function() {
      checkForUpdates();
  }, 1000);

  $('#version').on('click', function (event) {
      event.preventDefault();

      $.get('/data', function(data) {
          fancyRenderFunction(data.collections);
          initialState = data;

          changes.forEach(function (change) {
              applyChange(change);
          })
      });

  });

  $('#publish').on('click', function (event) {
      event.preventDefault();
      if (!changes.length) return;

      if (!username) {
          username = prompt('Please enter username');
      }

      const payload = {
          version: {
              user: username,
              version: Date.now() / 1000
          },
          changes: changes
      };

      $.post('/publish', JSON.stringify(payload), function (data) {
          initialState = data;
          fancyRenderFunction(data.collections);
          $('#status').text('Changes published.');
      }, 'json')
  })

});
