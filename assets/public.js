'use strict';
(function($) {

  $( () => {
    iFrameResize(
      {
        checkOrigin: false,
        log: false
      },
      '#renderer-problem'
    );

    $('#renderer-problem').on( 'load', function() {
      addFormListener();
      loadProblemAttribution();
    });
  } )

  // Fetch problem with random seed number
  $(document).on( 'click', '#wwpe-random-seed', function(e) {
    e.preventDefault();

    // Get iframe element
    let problemIframe = $('#renderer-problem')[0];

    // Get current problem source
    let problemId = $('#problemId').val();

    // Fetch HTML
    $.ajax({
      url: wwpe.ajax_url,
      method: 'POST',
      dataType: 'json',
      data: {
        'action': 'wwpe_get_problem_render_html',
        'problem_id': problemId
      }
    }).done( function(response ) {
      if(response.success) {
        $('#problemSeed').val(response.seed);
        $('#problemId').val(response.problem_id);
        problemIframe.srcdoc = response.html;
      }
    }).fail( function(xhr) {
      let response = xhr.responseJSON;
      console.log(`Can't generate new problem: ${response.data}`);
    })
  });

  // Attach listeners on the problem form
  function addFormListener() {
    // Get iframe element
    let problemIframe = $('#renderer-problem')[0];

    // Get iframe's form element
    let problemForm = problemIframe.contentWindow.document.getElementById('problemMainForm');

    if( ! problemForm ) {
      console.log('Could not find problem form!');
      return;
    }

    const isValidUrl = urlString=> {
	  	var urlPattern = new RegExp('^(https?:\\/\\/)?'+ // validate protocol
	    '((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.)+[a-z]{2,}|'+ // validate domain name
	    '((\\d{1,3}\\.){3}\\d{1,3}))'+ // validate OR ip (v4) address
	    '(\\:\\d+)?(\\/[-a-z\\d%_.~+]*)*'+ // validate port and path
	    '(\\?[;&a-z\\d%_.~+=-]*)?'+ // validate query string
	    '(\\#[-a-z\\d_]*)?$','i'); // validate fragment locator
	  return !!urlPattern.test(urlString);
	}

    // Attach submit event on the iframe's form
    $(problemForm).on('submit', function(e) {
      e.preventDefault();

      let formData = new FormData(this);
      let clickedButton = this.querySelector('.btn-clicked');
      let problemId = document.getElementById('problemId').value;
      let problemSeed = parseInt(document.getElementById('problemSeed').value);

      // formData.set('permissionLevel', 20);
      formData.set('includeTags', 1);
      formData.set('showComments', 1);
      
      if(isValidUrl(problemId)) {
        formData.set('problemSourceURL', problemId);
      } else {
        formData.set('sourceFilePath', problemId);
      }

      formData.set('problemSeed', problemSeed);
      formData.set('format', 'json');
      formData.set('outputFormat', 'single');
      formData.set(clickedButton.name, clickedButton.value);

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

  // Load problem attribution
  function loadProblemAttribution() {
    // Get problem source
    let problemId = $('#problemId').val();

    console.log("AIUJSHIUA")

    // Get problem seed
    let problemSeed = $('#problemSeed').val();

    // Fetch problem attributions
    $.ajax({
      url: wwpe.ajax_url,
      method: 'POST',
      dataType: 'json',
      data: {
        'action': 'wwpe_get_problem_attribution',
        'problem_id': problemId,
        'problem_seed': problemSeed
      }
    }).done( function(response ) {
      if(response.success) {
        $('.wwpe-problem-wrapper').append('<table id="wwpe-problem-attribution">');
        $.each(response.tags, function( index, item) {
          $('#wwpe-problem-attribution').append(`<tr><td>${index}</td><td>${item}</td></tr>`);
        });
        $('.wwpe-problem-wrapper').append('</table>');
      }
    }).fail( function(xhr) {
      let response = xhr.responseJSON;
      console.log(`Can't generate new problem: ${response.data}`);
    });
  }

})(jQuery);
