// Function to make an asynchronous call to a WordPress endpoint with a nonce
function fetchDataWithNonce() {
    return new Promise((resolve, reject) => {
        const nonce = wp_data.nonce; // Access the nonce from wp_data;
        const endpointUrl = wp_data.api_url; // Access the API URL from wp_data

        // Making a fetch request with the nonce
        fetch(endpointUrl, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': nonce,
            },
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                resolve(data);
            })
            .catch(error => {
                reject(error);
            });
    });
}

// Function to handle the button click event
function handleButtonClick() {
    document.getElementById('fetchButton').style.display = 'none';
    fetchDataWithNonce()
        .then(result => {
            console.log(result);
            updateWinnerBox(result);
            // Handle the result as needed
        })
        .catch(error => {
            console.error(error);
            // Handle errors
        });
    
}

function updateWinnerBox(winnerName) {
    const winnerResultDiv = document.getElementById('winnerResult');
    winnerResultDiv.textContent = winnerName;
    winnerResultDiv.classList.add('show');
}

// Attach the click event handler to the button
document.addEventListener('DOMContentLoaded', () => {
    const button = document.getElementById('fetchButton');
    button.addEventListener('click', handleButtonClick);
});

document.addEventListener('DOMContentLoaded', function () {
    // Check if the warning-box div is present
    var warningBox = document.querySelector('.warning-box');

    // If warning-box is present, disable the winnerBox div
    if (warningBox) {
        var winnerBox = document.getElementById('winnerBox');
        if (winnerBox) {
            winnerBox.disabled = true;
            winnerBox.hidden = true;

            // Disable all children elements of winnerBox
            var winnerBoxChildren = winnerBox.children;
            for (var i = 0; i < winnerBoxChildren.length; i++) {
                winnerBoxChildren[i].disabled = true;
            }
        }
    }
});