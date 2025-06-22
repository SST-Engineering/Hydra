using System;
using System.Collections.Generic;
using System.ComponentModel;
using System.Data;
using System.Drawing;
using System.Linq;
using System.Text;
using System.Windows.Forms;

namespace ALCW_Ticket_Runner
{
    public partial class receipt : Form
    {
        public receipt()
        {
            InitializeComponent();
        }
        private Ticket ticket;
        public Ticket Ticket
        {
            get { return this.ticket; }
            set { this.ticket = value; }
        }
        public void run(Ticket ticket)
        {
            this.Ticket = ticket;
            this.ReceiptProfile.Text = ticket.ToString();
            this._showReceipt();
            this.Show();
            this.ReceiptText.SelectionLength = 0;
            this.Refresh();
        }
        private void _showReceipt()
        {
            FtpGetReceipt get = new FtpGetReceipt();
            string t = this.Ticket.Url + "/" + this.Ticket.BaseDir + "/receipts/";
            t += this.Ticket.Profile;
            t += "/receipt.log";
            string s = get.run(t, this.Ticket.UName, this.Ticket.PW);
            this.ReceiptText.Text = s;
            this.ReceiptText.SelectionLength = 0;
        }

        private void Retry_Click(object sender, EventArgs e)
        {
            this._showReceipt();
            this.Refresh();
        }
        
    }
}
