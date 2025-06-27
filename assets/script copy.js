const hamBurger = document.querySelector(".toggle-btn");

hamBurger.addEventListener("click", function () {
  document.querySelector("#sidebar").classList.toggle("expand");
});

document.oncontextmenu =() => {
  alert("Don't try right click")
  return false
}

document.onkeydown = e => {
  if(e.key == "F12") {
    alert("Don't try to inspect element")
    return false
  }

  if(e.ctrlkey && e.key == "u") {
    alert("Don't try to view page sources")
    return false
  }

  if(e.ctrlkey && e.key == "c") {
    alert("Don't try to copy page element")
    return false
  }

  if(e.ctrlkey && e.key == "v") {
    alert("Don't try to paste anything to page")
    return false
  }
}