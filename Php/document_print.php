function printTicket() {
			var divContents = document.getElementById('invoice_print_bar').innerHTML;
	        var a = window.open('', 'Receipt', '');
	        a.document.write('<html><head><title>Invoice bar</title>');
	        a.document.write('<style type="text/css"> *, html {margin:0;padding:0;}</style>');
	        a.document.write('</head><body>');
	        a.document.write(divContents);
	        a.document.write('</body></html>');
	        a.document.close();
	        a.onload=function(){
	        	a.focus();
	        	a.print();
	        	a.close();
	        	//$("#header_ticket_logo").hide();
	        };
	    }