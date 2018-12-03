jQuery(document).ready(function($) {
  $("#myTable").DataTable({
    bSort: false,
    language: {
      sDecimal: ",",
      sEmptyTable: "Δεν υπάρχουν δεδομένα στον πίνακα",
      sInfo: "Εμφανίζονται _START_ έως _END_ από _TOTAL_ εγγραφές",
      sInfoEmpty: "Εμφανίζονται 0 έως 0 από 0 εγγραφές",
      sInfoFiltered: "(φιλτραρισμένες από _MAX_ συνολικά εγγραφές)",
      sInfoPostFix: "",
      sInfoThousands: ".",
      sLengthMenu: "Δείξε _MENU_ εγγραφές",
      sLoadingRecords: "Φόρτωση...",
      sProcessing: "Επεξεργασία...",
      sSearch: "Αναζήτηση:",
      sSearchPlaceholder: "Αναζήτηση",
      sThousands: ".",
      sUrl: "",
      sZeroRecords: "Δεν βρέθηκαν εγγραφές που να ταιριάζουν",
      oPaginate: {
        sFirst: "Πρώτη",
        sPrevious: "Προηγούμενη",
        sNext: "Επόμενη",
        sLast: "Τελευταία"
      },
      oAria: {
        sSortAscending: ": ενεργοποιήστε για αύξουσα ταξινόμηση της στήλης",
        sSortDescending: ": ενεργοποιήστε για φθίνουσα ταξινόμηση της στήλης"
      }
    }
  });
  $("#aristech_datepick1").datepicker({
    dateFormat: "yy-mm-dd"
  });
  $("#aristech_datepick2").datepicker({
    dateFormat: "yy-mm-dd"
  });
  $("#aristech_datepick3").datepicker({
    dateFormat: "yy-mm-dd"
  });
  $("#aristech_datepick4").datepicker({
    dateFormat: "yy-mm-dd"
  });
});
