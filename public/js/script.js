"use strict";
// --------------------------------------------------------------
// Position Fix
// --------------------------------------------------------------
origin = window.location.origin;
$(window).scroll(function() {
    if ($(document).scrollTop() > 50) {
        $('.nav').addClass('affix');
        $('.navTrigger').addClass('affix');
    } else {
        $('.nav').removeClass('affix');
        $('.navTrigger').removeClass('affix');
    }
});

// --------------------------------------------------------------
// Hamburger
// --------------------------------------------------------------
document.querySelector('.navTrigger').addEventListener('click', function() {
    this.classList.toggle('active');
    var mainListDiv = document.getElementById('mainListDiv');
    mainListDiv.classList.toggle('show_list');
    mainListDiv.style.display = 'block';
  });

// --------------------------------------------------------------
// Carousel
// --------------------------------------------------------------
const wrapper = document.querySelector(".wrapper");
const carousel = document.querySelector(".carousel");
if(carousel) {
    const firstCardWidth = carousel.querySelector(".card").offsetWidth;
    const arrowBtns = document.querySelectorAll(".wrapper i.arrow");
    const carouselChildrens = [...carousel.children];
    let isDragging = false, isAutoPlay = true, startX, startScrollLeft, timeoutId;
    let cardPerView = Math.round(carousel.offsetWidth / firstCardWidth);
    carousel.classList.add("no-transition");
    carousel.scrollLeft = carousel.offsetWidth;
    carousel.classList.remove("no-transition");
    arrowBtns.forEach(btn => {
        btn.addEventListener("click", () => {
            carousel.scrollLeft += btn.id == "left" ? -firstCardWidth : firstCardWidth;
        });
    });
    const dragStart = (e) => {
        isDragging = true;
        carousel.classList.add("dragging");
        startX = e.pageX;
        startScrollLeft = carousel.scrollLeft;
    }
    const dragging = (e) => {
        if(!isDragging) return; 
        carousel.scrollLeft = startScrollLeft - (e.pageX - startX);
    }
    const dragStop = () => {
        isDragging = false;
        carousel.classList.remove("dragging");
    }
    const infiniteScroll = () => {
        if(carousel.scrollLeft === 0) {
            carousel.classList.add("no-transition");
            carousel.scrollLeft = carousel.scrollWidth - (2 * carousel.offsetWidth);
            carousel.classList.remove("no-transition");
        }
        else if(Math.ceil(carousel.scrollLeft) === carousel.scrollWidth - carousel.offsetWidth) {
            carousel.classList.add("no-transition");
            carousel.scrollLeft = carousel.offsetWidth;
            carousel.classList.remove("no-transition");
        }
        clearTimeout(timeoutId);
        if(!wrapper.matches(":hover")) autoPlay();
    }
    const autoPlay = () => {
        if(window.innerWidth < 800 || !isAutoPlay) return; 
        timeoutId = setTimeout(() => carousel.scrollLeft += firstCardWidth, 2500);
    }

    autoPlay();
    carousel.addEventListener("mousedown", dragStart);
    carousel.addEventListener("mousemove", dragging);
    document.addEventListener("mouseup", dragStop);
    carousel.addEventListener("scroll", infiniteScroll);
    wrapper.addEventListener("mouseenter", () => clearTimeout(timeoutId));
}

// --------------------------------------------------------------
// Modal - Publish / Unpublish my plant
// --------------------------------------------------------------
function pub_unpub_plant(plant_id, is_published) {
    return new Promise(function(resolve, reject) {
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
            if (this.readyState == 4) {
                if (this.status == 200) {
                    resolve(this.responseText);
                } else {
                    reject(new Error('Error: ' + this.status));
                }
            }
        };
        xhttp.open("GET", origin+"/plant/publish/" + plant_id + "/" + is_published, true);
        xhttp.send();
    });
}

function publish_modal() {
    const openModalButton = event.target;
    const publishModal = document.getElementById('clickforPublishModal');
    let plant_id = openModalButton.getAttribute('data-plant-id');
    const saveButton = document.getElementById('publishButton');
    const closePublishModalButton = document.getElementById('closePublishModalButton');
    saveButton.addEventListener('click', function() {
        pub_unpub_plant(plant_id, true)
            .then(function(result) {
                console.log("ok");
                closePublishModalButton.click();
                // alert("Votre plante a bien été publiée");
                Swal.fire({
                    icon: 'success',
                    title: 'Votre plante a bien été publiée !',
                    confirmButtonColor: '#15803d',
                })
                .then((result) => {
                    if (result.isConfirmed) {                        
                        location.reload();
                    }});
            })
            .catch(function(error) {
                console.error(error);
                // alert("Une erreur est survenue lors de la publication de votre plante");
                Swal.fire({
                    icon: 'error',
                    title: 'Une erreur est survenue lors de la publication de votre plante',
                    confirmButtonColor: '#15803d',
                })
                .then((result) => {
                    if (result.isConfirmed) {                        
                        location.reload();
                    }}
                );
            });
    });
}

function unpublish_modal() {
    const openModalButton = event.target;
    const unpublishModal = document.getElementById('clickforUnpublishModal');
    let plant_id = openModalButton.getAttribute('data-plant-id');
    const saveButton = document.getElementById('unpublishButton');
    const closeUnpublishModalButton = document.getElementById('closeUnpublishModalButton');
    saveButton.addEventListener('click', function() {
        pub_unpub_plant(plant_id, false)
            .then(function(result) {
                console.log("ok");
                closeUnpublishModalButton.click();
                // alert("Votre plante a bien été dépubliée");
                Swal.fire({
                    icon: 'success',
                    title: 'Votre plante a bien été dépubliée !',
                    confirmButtonColor: '#15803d',
                })
                .then((result) => {
                    if (result.isConfirmed) {
                        location.reload();
                    }});
            })
            .catch(function(error) {
                console.error(error);
                // alert("Une erreur est survenue lors de la dépublication de votre plante");
                Swal.fire({
                    icon: 'error',
                    title: 'Une erreur est survenue lors de la dépublication de votre plante',
                    confirmButtonColor: '#15803d',
                })
                .then((result) => {
                    if (result.isConfirmed) {                        
                        location.reload();
                    }}
                );
            });
    });
}

// --------------------------------------------------------------
// Modal - Modify my plant
// --------------------------------------------------------------
async function modify_modal() {
    const openModalButton = event.target;
    const modifyModal = document.getElementById('clickforModifyModal');
    let plant_id = openModalButton.getAttribute('data-plant-id');
    const formElement = await document.getElementById('modifyForm');
    let formaction = formElement.getAttribute('action');
    formaction = formaction.replace('1', plant_id);
    formElement.setAttribute('action', formaction);
    //do an xhr call to get the plant data to fill the form
    const xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4) {
            if (this.status == 200) {
                const plant = JSON.parse(this.responseText);
                const plantName = document.getElementById('plant_name2');
                const scientificName = document.getElementById('scientific_name2');
                const familyName = document.getElementById('family_name2');
                const gbifId = document.getElementById('gbif_id2');
                const birth = document.getElementById('birth2');
                const plantDescription = document.getElementById('description2');
                const environment2 = document.getElementById('environment2');
                const image_input = document.getElementById('input_photo_modify');
                const image_input_hidden = document.getElementById('imgb64_modify');
                const image_preview = document.getElementById('img_modify');

                plantName.value = plant.plant_name;
                scientificName.value = plant.scientific_name;
                familyName.value = plant.family_name;
                gbifId.value = plant.gbif_id;
                birth.value = plant.birth;
                plantDescription.value = plant.description;
                if (plant.environment == 0) {
                    environment2.checked = false;
                } else {
                    environment2.checked = true;
                }
                image_input_hidden.value = plant.image;
                image_preview.src = plant.image;
            } else {
                reject(new Error('Error: ' + this.status));
            }
        }
    };
    xhttp.open("GET", origin+"/plant/get/"+plant_id, true);
    xhttp.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
    xhttp.send();

    const saveButton = document.getElementById('modifyButton');
    const closeModifyModalButton = document.getElementById('closeModifyModalButton');
    saveButton.addEventListener('click', function() {
        closeModifyModalButton.click();
        window.location.href = origin+"/plant/modify/" + plant_id;
    });
}

// --------------------------------------------------------------
// Modal - delete my plant
function delete_modal(){
    const openModalButton = event.target;
    const deleteModal = document.getElementById('clickforDeleteModal');
    let plant_id = openModalButton.getAttribute('data-plant-id');
    const saveButton = document.getElementById('deleteButton');
    saveButton.addEventListener('click', function() {
        //do an xhr call to delete the plant
        const xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
            if (this.readyState == 4) {
                if (this.status == 200) {
                    console.log("ok");
                    // alert("Votre plante a bien été supprimée");
                    Swal.fire({
                        icon: 'success',
                        title: 'Votre plante a bien été supprimée !',
                        confirmButtonColor: '#15803d',
                    })
                    .then((result) => {
                        if (result.isConfirmed) {
                            location.reload();
                        }});
                } else {
                    console.error(error);
                    // alert("Une erreur est survenue lors de la suppression de votre plante");
                    Swal.fire({
                        icon: 'error',
                        title: 'Une erreur est survenue lors de la suppression de votre plante',
                        confirmButtonColor: '#15803d',
                    })
                    .then((result) => {
                        if (result.isConfirmed) {                        
                            location.reload();
                        }}
                    );
                }
            }
        }
        xhttp.open("POST", origin+"/plant/delete/"+plant_id, true);
        xhttp.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
        xhttp.send();
    });
}
// --------------------------------------------------------------

// --------------------------------------------------------------
// Modal - Send a message
// --------------------------------------------------------------
const sendMessageModal = document.getElementById('sendMessageModal')
var messageTextArea = document.getElementById('message-text');
var sendButton = document.getElementsByClassName('btn-send')[0];
let recipient = "";
let plantName = "";
if(sendMessageModal != null && messageTextArea != null && sendButton != null) {
    sendMessageModal.addEventListener('show.bs.modal', event => {

        const button = event.relatedTarget;
        recipient = button.getAttribute('data-to-owner');
        plantName = button.getAttribute('data-plant-name');
      
        const modalTitle = sendMessageModal.querySelector('.modal-title')
        modalTitle.textContent = `New message to ${recipient}`
        messageTextArea.value = "";
      
      })
      
      sendButton.addEventListener('click', function() {
          sendMail(recipient, plantName);
      })
      
      function sendMail(recipient,plantName) {
          var message = document.getElementById("message-text").value;
          let subject = "Message à propos de votre plante " + plantName 
          var mailtoLink = "mailto:"+recipient+"?subject="+subject+"&body=" + encodeURIComponent(message);
          window.location.href = mailtoLink;
      }
}

// --------------------------------------------------------------
// Modal - Upload
// --------------------------------------------------------------
const uploadModal = document.getElementById('uploadModal')
if(uploadModal != null) {
    uploadModal.addEventListener('show.bs.modal', event => {
    const button = event.relatedTarget
    const owner = button.getAttribute('data-owner-id')
    const modalTitle = uploadModal.querySelector('.modal-title')

    modalTitle.textContent = 'Uploader une plante'
    })
}
// --------------------------------------------------------------
// Tooltip
// --------------------------------------------------------------
const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]')
const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl))

// --------------------------------------------------------------
// Display uploading Image
// --------------------------------------------------------------
function readFile(input_element) {
    return new Promise((resolve, reject) => {
        if (!input_element.files || !input_element.files[0]) {
            reject(new Error('No file selected.'));
            return;
        }
        const FR = new FileReader();
        FR.addEventListener("load", function(evt) {
            resolve(evt.target.result);
        }); 
        FR.addEventListener("error", function(evt) {
            reject(new Error('Error reading file.'));
        });
        FR.readAsDataURL(input_element.files[0]);
    });
}
if(document.querySelector("#input_photo_upload") != null) {
    document.querySelector("#input_photo_upload").addEventListener("change",event => {
        const img_preview = document.querySelector("#img_upload")
        const imgb64 = document.querySelector("#imgb64_upload")
        readFile(event.target)
        .then((b64) => {
            img_preview.src = b64;
            imgb64.value = b64;
        })
        .catch((error) => {
            console.error(error); // Gérer toute erreur ici
        });
    });

    function dataURLtoFile(dataurl, filename) {
        const arr = dataurl.split(',');
        const mime = arr[0].match(/:(.*?);/)[1];
        const bstr = atob(arr[1]);
        let n = bstr.length;
        const u8arr = new Uint8Array(n);
        while(n--){
            u8arr[n] = bstr.charCodeAt(n);
        }
        return new File([u8arr], filename, {type: mime});
    }

    function check_if_plant_in_input(input_element){
        return readFile(input_element)
        .then(async(b64) => {
            try {
                const formData = new FormData();
                let file = dataURLtoFile(b64, 'image.png');
                formData.append('image', file);

                const response = await fetch(origin+'/plant/identify', {method: 'POST',body: formData});
                if (response.status === 204) {
                    return false;
                } else if (response.ok) {
                    return true;
                } else {
                    throw new Error('Identification request failed');
                }
            } catch (error) {
                console.error(error);
                throw error;
            }
        });
    }



    document.querySelector("#input_photo_modify").addEventListener("change", async function(event) {
        const img_preview = document.querySelector("#img_modify")
        const imgb64 = document.querySelector("#imgb64_modify")
        const loadingModal = Swal.fire({
            title: 'Identification en cours par notre IA !',
            html: '<div class="text-center"><div class="spinner-border" role="status"></div><h3></h3></div>',
            allowOutsideClick: false,
            showConfirmButton: false,
            onBeforeOpen: () => {
                Swal.showLoading();
            },
        });
        let is_plant = await check_if_plant_in_input(event.target);
        if (is_plant) {
            readFile(event.target)
            .then((b64) => {
                img_preview.src = b64;
                imgb64.value = b64;
            })
            .catch((error) => {
                console.error(error); // Gérer toute erreur ici
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                html: '<h3>Aucune plante n\'a été détectée !</h3>',
                confirmButtonColor: '#15803d',
            });
            event.target.value = "";
        }
        loadingModal.close();
    });

    // --------------------------------------------------------------
    // PlantNet API
    // --------------------------------------------------------------


    document.querySelector("#input_photo_upload").addEventListener("change", async function(e) {
        const file = e.target.files[0];
        console.log(file);
        if (!file) return;

        const formData = new FormData();
        formData.append('image', file);

        const loadingModal = Swal.fire({
            title: 'Identification en cours',
            html: '<div class="text-center"><div class="spinner-border" role="status"></div><h3>Identification en cours par notre IA!</h3></div>',
            allowOutsideClick: false,
            showConfirmButton: false,
            onBeforeOpen: () => {
                Swal.showLoading();
            },
        });

        try {
            const response = await fetch(origin+'/plant/identify', {method: 'POST',body: formData});

            if (response.status === 204) {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    html: '<h3>Aucune plante n\'a été détectée !</h3>',
                    confirmButtonColor: '#15803d',
                });
                document.getElementById("inp").value = "";
                document.getElementById("img").src = "";
            } else if (response.ok) {
                const jsonResponse = await response.json();
                const plantResult = jsonResponse.results[0];

                const plantNameInput = document.querySelector("#plant_name");
                const scientificNameInput = document.querySelector("#scientific_name");
                const familyNameInput = document.querySelector("#family_name");
                const gbifIdInput = document.querySelector("#gbif_id");

                plantNameInput.value = plantResult.species.commonNames[0];
                scientificNameInput.value = plantResult.species.scientificNameWithoutAuthor;
                familyNameInput.value = plantResult.species.family.scientificNameWithoutAuthor;
                if(plantResult.gbif != null){
                    gbifIdInput.value = plantResult.gbif.id;
                } else {
                    gbifIdInput.value = "0";
                }
                document.getElementById("aifields").classList.remove("d-none");
            } else {
                throw new Error('Identification request failed');
            }
        } catch (error) {
            console.error(error);
            // Handle error case here
        } finally {
            // Close the loading modal
            loadingModal.close();
        }
    });
}
// --------------------------------------------------------------
// Icon Change on Hover
// --------------------------------------------------------------
var envelopeIcon = document.getElementById('envelope-icon');

envelopeIcon.addEventListener('mouseover', function() {
  envelopeIcon.classList.remove('fa-envelope');
  envelopeIcon.classList.add('fa-envelope-open');
});

envelopeIcon.addEventListener('mouseout', function() {
  envelopeIcon.classList.remove('fa-envelope-open');
  envelopeIcon.classList.add('fa-envelope');
});