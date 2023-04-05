window.addEventListener( 'load', () => {
    let problemIframe = document.getElementById('renderer-problem');
    let problemForm = problemIframe.contentWindow.document.getElementById('problemMainForm');

    if(!problemForm) {
        console.log('Could not find form! Has a problem been rendered?');
        return;
    }

    problemForm.addEventListener( 'submit', (e) => {
        e.preventDefault();
        
        let formData = new FormData(problemForm);
        let clickedButton = problemForm.querySelector('.btn-clicked');

        formData.set('permissionLevel', 20);
        formData.set('includeTags', 1);
        formData.set('showComments', 1);
        formData.set('sourceFilePath', document.getElementById('problemId').value);
        formData.set('problemSeed', parseInt(document.getElementById('problemSeed').value));
        formData.set('format', 'json');
        formData.set('outputFormat', 'single');
        formData.set(clickedButton.name, clickedButton.value);
        formData.set('problemSource', document.getElementById('problemSource').value);

        const submitUrl = 'http://localhost:3000/render-api';
        const submitParams = {
            method: 'POST',
            body: formData
        };

        for (const pair of formData.entries()) {
            console.log(`${pair[0]}, ${pair[1]}`);
          }

        fetch(submitUrl, submitParams).then(function(response) {
            if (response.ok) {
                return response.json();
            } else {
              throw new Error("Could not reach the API: " + response.statusText);
            }
          }).then(function(data) {
            // console.log(data);
            problemIframe.srcdoc = data.renderedHTML;
            if (data.debug.errors !== "") {
              alert(data.debug.errors.replace(/<br\/>/,"\n"));
            }
          }).catch(function(error) {
            document.getElementById("renderer-problem").innerHTML = error.message;
          });
    });
});