'use strict';
(function($) {

  $( () => {
    iFrameResize(
      {
        checkOrigin: false,
        log: false
      },
      '.renderer-problem'
    );
    
    // Add form listeners for every problem
    let iframes = $('.renderer-problem');
    iframes.each( function() {
      $(this).on('load', function() {
        addFormListener($(this).attr('data-id'));
      })
    })
  } )

  // Get problem with random seed number
  $(document).on( 'click', '.wwpe-random-seed', function(e) {
    e.preventDefault();

    // Get block id
    let blockId = $(this).closest('.wwpe-problem-wrapper').attr('data-id');

    // Get iframe element
    let iframe = $(`.renderer-problem[data-id="${blockId}"]`)[0];

    // Get problem id
    let problemId = $(`#problemId-${blockId}`).val();

    // Get problem HTML
    $.ajax({
      url: wwpe.ajax_url,
      method: 'POST',
      dataType: 'json',
      data: {
        'action': 'wwpe_get_problem_render_html',
        'problem_id': problemId
      }
    }).done( function(response) {
      if(response.success) {
        $(`#problemSeed-${blockId}`).val(response.seed);
        iframe.srcdoc = response.html;
      }
    }).fail( function(xhr) {
      let response = xhr.responseJSON;
      console.log(`Can't generate new problem: ${response.data}`);
    });
  });

  // Attach listeners on the problem form
  function addFormListener(blockId) {
    // Get iframe
    let iframe = $(`.renderer-problem[data-id="${blockId}"]`)[0];

    // Get iframe's form element
    let problemForm = iframe.contentWindow.document.getElementById('problemMainForm');
    
    // Abort if form doesn't exist
    if( ! problemForm ) {
      console.log('Could not find problem form!');
      return;
    }

    // Submit problem form
    $(problemForm).on('submit', function(e) {
      e.preventDefault();
      
      let formData = new FormData(this);
      let clickedButton = this.querySelector('.btn-clicked');
      let problemId = $(`#problemId-${blockId}`).val();
      let problemSeed = parseInt($(`#problemSeed-${blockId}`).val());

      if(isValidUrl(problemId)) {
        formData.set('problemSourceURL', problemId);
      } else {
        formData.set('sourceFilePath', problemId);
      }
      
      formData.set('problemSeed', problemSeed);
      formData.set('format', 'json');
      formData.set('outputFormat', 'single');
      formData.set(clickedButton.name, clickedButton.value);
      formData.set('includeTags', 1);
      formData.set('showComments', 1);

      $.ajax({
        url: wwpe.endpoint_url,
        method: 'POST',
        dataType: 'json',
        processData: false,
        contentType: false,
        data: formData
      }).done( function(response) {
        // Display response in the iframe
        iframe.srcdoc = response.renderedHTML;
      }).fail( function(xhr) {
        // On fail, remove the iframe and display the error message
        iframe.remove();
        $(`.wwpe-problem-content[data-id="${blockId}"]`).html(xhr.responseText);
      })

    });
  }

  // Validate URL
  function isValidUrl(url) {
    var urlPattern = new RegExp('^(https?:\\/\\/)?'+ // validate protocol
      '((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.)+[a-z]{2,}|'+ // validate domain name
      '((\\d{1,3}\\.){3}\\d{1,3}))'+ // validate OR ip (v4) address
      '(\\:\\d+)?(\\/[-a-z\\d%_.~+]*)*'+ // validate port and path
      '(\\?[;&a-z\\d%_.~+=-]*)?'+ // validate query string
      '(\\#[-a-z\\d_]*)?$','i'); // validate fragment locator

    return !!urlPattern.test(url);
  }
})(jQuery);
