'use strict';
(function($) {
  
  $(document).on('ready', function() {
    iFrameResize(
      {
        checkOrigin: false,
        log: false
      }, 
      '#renderer-problem'
    );

    $('#renderer-problem').load( function() {
      addFormListener();
    });
  });

  // Fetch problem with random seed number
  $(document).on( 'click', '#wwpe-random-seed', function(e) {
    e.preventDefault();
    
    // Get iframe element
    let problemIframe = $('#renderer-problem')[0];

    // Get current problem source
    let problemSource = $('#problemSource').val();

    // Fetch HTML
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
        $('#problemSeed').val(response.seed);
        $('#problemSource').val(response.source);
        problemIframe.srcdoc = response.html;
      }
    }).fail( function(xhr) {
      let response = xhr.responseJSON;
      console.log(`Can't generate new problem: ${response.data}`);
    })
  });

  // Submit 
  function addFormListener() {
    // Get iframe element
    let problemIframe = $('#renderer-problem')[0];

    // Get iframe's form element
    let problemForm = problemIframe.contentWindow.document.getElementById('problemMainForm');

    if( ! problemForm ) {
      console.log('Could not find problem form!');
      return;
    }

    // Attach submit event on the iframe's form
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

      // Submit problem answers
      $.ajax({
        url: wwpe.endpoint_url,
        method: 'POST',
        dataType: 'json',
        processData: false,
        contentType: false,
        data: formData,
      }).done( function(response) {
        // Display response in the iframe
        problemIframe.srcdoc = response.renderedHTML;
      }).fail( function(xhr) {
        // If problem with the submission, remove iframe and display the returned error message
        problemIframe.remove();
        $('.wwpe-problem-content').html(xhr.responseText);
      })
    })
  }
})(jQuery);
