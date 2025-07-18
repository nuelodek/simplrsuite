function openCloudModal() {
  var cloudModal = document.getElementById("cloudModal");
  cloudModal.style.display = "block";
  loadCloudData();
}

function loadCloudData() {
  // AJAX call to fetch cloud data
  fetch("../../users/admin/fetch_cloud_data.php")
      .then(response => response.text())
      .then(data => {
          document.getElementById("cloudContent").innerHTML = data;
      })
      .catch(error => {
          console.error("Error fetching cloud data:", error);
      });
}
