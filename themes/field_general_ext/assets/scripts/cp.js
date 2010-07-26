(function($) {

$(document).ready(function() {
  $('tr.weblog-head')
  .click(function(event) {
      var $checkbox = $(this).find('label.expander input'),
          cb = $checkbox[0];
          
    if (!$(event.target).closest('label.expander').length) {
      cb.checked = !cb.checked;
    }

    var collapse = !$checkbox.is(':checked');
    $(this).closest('table').toggleClass('collapsed', collapse);
    
  });
    
  
  $('table.table-sortable').children('tbody').each(function(index) {
    var $tbody = $(this);
    var $save = $('#save_settings').clone().attr('id', function() {
      this.id + index;
    }).wrap('<div class="box"></div>').parent();
    
    $tbody.parent().after($save);

    // set "disabled" state of row based on checked radio
    // and other initializing goodness
    $tbody.children()
    .toggleDisabled()
    .bind('mouseenter', setCellWidths)
      .find('input.sequence')
      .each(function(index) {
        $(this).val(index + 1);
      });

    // on click, toggle "disabled" state of row based on checked radio
    $tbody.bind('click.fgeneral', function(event) {
      $(event.target).closest('tr').toggleDisabled();
    });

    // make each channel's fields sortable
    $tbody.sortable({
      start: function(event, ui) {
        var $helperCells = $(ui.helper).find('td');
        if ($helperCells.length) {
          $helperCells.each(function() {
            $(this).width( $(this).data('width') );
          });
        }

      },
      containment: 'parent',
      axis: 'y',
      forcePlaceholderSize: true,
      cancel: 'input, label, a',
      cursor: 'move',
      stop: function(event, ui) {

        // workaround for crazy Firefox bug in which first click on
        // a radio after dropping a row focuses the radio but does not
        // set it to checked
        $(ui.item).find('input:radio:checked').trigger('click');


        $tbody.children('tr')
        .each(function(index) {
          var $row = $(this);

          // reset row striping...
          $row.find('td')
          .removeClass('tableCellOne tableCellTwo')
          .addClass(index % 2 ? 'tableCellTwo' : 'tableCellOne');

          // set row sequence
          $row.find('.sequence').val(index + 1);

        })
        // rebind cellwidth setting
        .unbind('mouseenter', setCellWidths)
        .bind('mouseenter', setCellWidths);
      }
    });

  });


});

function setCellWidths() {
  $(this).find('td').each(function() {
    $(this).data('width', $(this).width());
  });
}

$.fn.toggleDisabled = function(cls) {
  cls = cls || 'disabled';
  return this.each(function(index) {
    var $this = $(this);
    $this.toggleClass('disabled', $this.find('input:radio:checked').val() !== 'y');
  });

};

})(jQuery);
