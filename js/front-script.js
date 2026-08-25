document.addEventListener("DOMContentLoaded", function () {
  var modal = document.getElementById("sec-modal");
  var mDate = document.getElementById("sec-modal-date");
  var mBody = document.getElementById("sec-modal-body");
  var mClose = document.querySelector(".sec-modal-close");

  document.querySelectorAll(".sec-table td.has-event").forEach(function (cell) {
    cell.addEventListener("click", function () {
      mDate.innerText = this.getAttribute("data-date") + " の予定";
      var labels = this.getAttribute("data-labels").split(",");
      var timeValues = this.getAttribute("data-times").split(",");
      var colorValues = this.getAttribute("data-colors").split(",");

      var htmlContent = "";
      for (var i = 0; i < labels.length; i++) {
        if (labels[i]) {
          var tDisplay =
            timeValues[i] ?
              '<p class="sec-modal-time">⏰ ' + timeValues[i] + "</p>"
            : "";
          // 動的な色を左のボーダーに適用
          var colorStyle = "border-left: 5px solid " + colorValues[i] + ";";
          htmlContent +=
            '<div class="sec-modal-item" style="' +
            colorStyle +
            '"><h4>' +
            labels[i] +
            "</h4>" +
            tDisplay +
            "</div>";
        }
      }
      mBody.innerHTML = htmlContent;
      modal.style.display = "flex";
    });
  });

  if (mClose) {
    mClose.addEventListener("click", function () {
      modal.style.display = "none";
    });
  }
  window.addEventListener("click", function (e) {
    if (e.target == modal) {
      modal.style.display = "none";
    }
  });
});
