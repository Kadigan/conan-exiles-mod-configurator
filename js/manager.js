function promiseRequest(requestUrl, isBinary){ if ( typeof isBinary == 'undefined' ) isBinary = false; else isBinary = true; let promise = $.Deferred(); let params = {}; params.method = 'GET'; if ( isBinary ){ params.dataType = 'binary'; params.processData = 'false'; params.responseType = 'arraybuffer'; } promise.functionParams = {'isBinary': isBinary, 'requestUrl': requestUrl}; promise.xhrParams = params; params.error = function(xhrObj, text, error){ let actionResult = xhrObj.responseText; try { actionResult = $.parseJSON(xhrObj.responseText) } catch (error){} promise.reject({status: xhrObj.status, path: requestUrl, error: actionResult, xhr: this}); }; params.success = function(data, text, xhrObj){ let actionResult; if ( promise.functionParams.isBinary ){ actionResult = data; } else { actionResult = xhrObj.responseText; try { actionResult = $.parseJSON(xhrObj.responseText) } catch (error){} } promise.resolve({status: text, path: requestUrl, data: actionResult, xhr: this}); }; $.ajax(requestUrl, params); return promise; };

class SpinnerClass {
    constructor(){ this.itemsRemaining = 0; this.locked = false; }
    lock(){ var items = 0; if ( arguments.length > 0 && typeof arguments[0] === "number" && Number.isInteger(arguments[0]) && arguments[0] > 0 ) this.itemsRemaining += arguments[0]; jQuery('#spinner-body').fadeIn(100); jQuery('body').addClass('noscroll'); }
    unlock(){ if ( this.itemsRemaining ) this.itemsRemaining--; if ( this.itemsRemaining ) return; jQuery('#spinner-body').fadeOut(100); jQuery('body').removeClass('noscroll'); }
}
var Spinner = new SpinnerClass();

function showError(msg, msg2){ return Swal.fire({ icon: 'error', html: msg, footer: msg2}); }
function showSuccess(msg, msg2){ return Swal.fire({ icon: 'success', html: msg, footer: msg2}); }

function produceListItem(item){
   let itemHTML = "<div class='item' data-id='" + item['id'] + "'>";
   itemHTML    += "<p>" + (item['steam_name'] != "" && typeof item['steam_name'] != 'undefined' ? item['steam_name'] : item['pak_name']) + "</p>";

   if ( item['type'] == 1 ){ // STEAM MODS
      if ( item['steam_state'] == 9 ){
         itemHTML    += "<p class='item-removed'>ITEM REMOVED FROM STEAM</p>";
      } else {
         itemHTML    += "<a href=\"https://steamcommunity.com/sharedfiles/filedetails/?id=" + item['id'] + "\" target=\"_blank\"><i class=\"fab fa-steam\"></i></a>";
         if ( item['update_required'] == 1 ){
            itemHTML    += "<p class='update-required'>UPDATE REQUIRED</p>";
         }
      }
   }

   // finalise
   itemHTML    += "</div>";
   return itemHTML;
}

jQuery(document).ready(function($){
   Spinner.lock();
   let modlist = promiseRequest("api/read");
   let mods    = promiseRequest("api/list");
   $.when(modlist, mods).fail(function(){ Spinner.unlock(); showError("At least one network request failed; try again later."); }).then(function(modlist, mods){
      if ( mods.data.result != "success" ){
         Spinner.unlock();
         showError(mods.data.msg, "This error requires direct intervention in the mod files.");
         return;
      }


      if ( modlist.data.data == null )
         modlist.data.data = [];

      if ( modlist.data.result != "success" ){
         Spinner.unlock();
         showError(mods.data.msg, "This error requires direct intervention in the mod files.");
         return;
      }

      // arrays
      let modsEnabled   = modlist.data.data;
      let modsAvailable = []; // this is the list of unused mods

      // objects
      let modsInstalled = mods.data.data; // this holds data for all our mods
      for(key in modsInstalled){
         if ( !modsInstalled.hasOwnProperty(key) )
            continue;

         if ( !modsEnabled.includes(key) ){
            modsAvailable.push(key);
         }
      }

      for(let i = 0; i < modsEnabled.length; i++){
         if ( !modsInstalled.hasOwnProperty(modsEnabled[i]) ){
            Spinner.unlock();
            showError("modlist.txt includes mod ID <b>" + modlist.data[i] + "</b> that isn't on the list of available mods.<br />Perhaps it has since been uninstalled/deleted, or renamed?", "Manual intervention is required in the modlist.txt file. Please correct the situation and retry running the manager.");
            return;
         }
      }

      Spinner.unlock();

      // populate the two lists
      $('#mods-enabled').empty();
      $('#mods-available').empty();
      for(let i = 0; i < modsEnabled.length; i++){ $('#mods-enabled').append(produceListItem(modsInstalled[modsEnabled[i]])); }
      for(let i = 0; i < modsAvailable.length; i++){ $('#mods-available').append(produceListItem(modsInstalled[modsAvailable[i]])); }

      $("#button-save").on("click", function(e){
         e.preventDefault();
         let selectedItems = $("#mods-enabled").find(".item");
         let modsList = [];
         for(let i = 0; i < selectedItems.length; i++){
            let itemID = $(selectedItems[i]).data('id');
            modsList.push(itemID);
         }
         Spinner.lock();
         let modsSaved = promiseRequest("api/save/" + modsList.join(","));
         $.when(modsSaved).fail(function(){ Spinner.unlock(); showError("Network request failed; try again later."); }).then(function(modsSaved){
            if ( modsSaved.data.result != "success" ){
               Spinner.unlock();
               showError(modsSaved.data.msg);
               return;
            }
            Spinner.unlock();
            showSuccess("The modlist.txt file has been <b>updated</b>.", "Please allow your system up to 30 seconds to update the file Conan:&nbsp;Exiles will see, as disk buffers may not be flushed immediately.")
         });
      });

      $("#button-clear").on("click", function(e){
         e.preventDefault();
         let modsCleared = promiseRequest("api/clear");
         $.when(modsCleared).fail(function(){ Spinner.unlock(); showError("Network request failed; try again later."); }).then(function(modsCleared){
            if ( modsCleared.data.result != "success" ){
               Spinner.unlock();
               showError(modsCleared.data.msg);
               return;
            }
            Spinner.unlock();
            showSuccess("The modlist.txt file has been <b>cleared</b> and is now empty.<br /><b>The page will now reload.</b>", "Please allow your system up to 30 seconds to update the file Conan:&nbsp;Exiles will see, as disk buffers may not be flushed immediately.").then(function(result){
               location.reload();
            });
         });
      });

      $("#button-enable-all").on("click", function(e){
         e.preventDefault();
         $('#mods-enabled').empty();
         $('#mods-available').empty();
         for(let i = 0; i < modsAvailable.length; i++){ $('#mods-enabled').append(produceListItem(modsInstalled[modsAvailable[i]])); }
         for(let i = 0; i < modsEnabled.length; i++){ $('#mods-enabled').append(produceListItem(modsInstalled[modsEnabled[i]])); }
      });


      new Sortable(document.getElementById("mods-enabled"), {
          group: 'shared',
          ghostClass: 'sortable-ghost-green',
          animation: 150
      });

      new Sortable(document.getElementById("mods-available"), {
          group: 'shared',
          ghostClass: 'sortable-ghost-red',
          animation: 150
      });
   });
});