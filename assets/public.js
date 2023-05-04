'use strict';
(function($) {
  
  $(document).on('ready', function() {
    let problemIframe = $('#renderer-problem');
    iFrameResize(
      {
        checkOrigin: false,
        log: false
      }, 
      '#renderer-problem'
    );

    problemIframe.load( function() {
      addFormListener();
    });
  });

  $(document).on( 'click', '#random-seed-button', function(e) {
    e.preventDefault();
    
    let problemIframe = $('#renderer-problem')[0];
    let problemSource = $('#problemSource').val();

    $.ajax({
      url: wwpe.ajax_url,
      method: 'POST',
      dataType: 'json',
      data: {
        'action': 'wwpe_get_problem_render_html',
        'problem_source': problemSource
      }
    }).done( function(response ) {
      if(response.success) {
        $('#problemSeed').val(response.problemSeed);
        $('#problemSource').val(response.problemSource);
        problemIframe.srcdoc = response.problemHtml;
      }
    }).fail( function(xhr) {
      let response = xhr.responseJSON;
      alert(`Can't generate new problem: ${response.data}`);
    })
  });

  function addFormListener() {
    let problemIframe = $('#renderer-problem')[0];
    let problemForm = problemIframe.contentWindow.document.getElementById('problemMainForm');

    if(!problemForm) {
      console.log('Could not find problem form!');
      return;
    }

    $(problemForm).on('submit', function(e) {
      e.preventDefault();

      let formData = new FormData(this);
      let clickedButton = this.querySelector('.btn-clicked');

      formData.set('permissionLevel', 20);
      formData.set('includeTags', 1);
      formData.set('showComments', 1);
      formData.set('sourceFilePath', document.getElementById('problemId').value);
      formData.set('problemSeed', parseInt(document.getElementById('problemSeed').value));
      formData.set('format', 'json');
      formData.set('outputFormat', 'single');
      formData.set(clickedButton.name, clickedButton.value);
      formData.set('problemSource', document.getElementById('problemSource').value);

      $.ajax({
        url: wwpe.endpoint_url,
        method: 'POST',
        dataType: 'json',
        processData: false,
        contentType: false,
        data: formData,
      }).done( function(response) {
        problemIframe.srcdoc = response.renderedHTML;
      }).fail( function(xhr) {
        problemIframe.remove();
        $('.wwpe-problem-content').html(xhr.responseText);
      })
    })
  }
})(jQuery);
