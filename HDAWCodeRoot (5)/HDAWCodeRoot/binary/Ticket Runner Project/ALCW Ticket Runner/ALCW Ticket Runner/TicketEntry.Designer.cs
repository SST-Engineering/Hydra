namespace ALCW_Ticket_Runner
{
    partial class TicketEntry
    {
        /// <summary>
        /// Required designer variable.
        /// </summary>
        private System.ComponentModel.IContainer components = null;

        /// <summary>
        /// Clean up any resources being used.
        /// </summary>
        /// <param name="disposing">true if managed resources should be disposed; otherwise, false.</param>
        protected override void Dispose(bool disposing)
        {
            if (disposing && (components != null))
            {
                components.Dispose();
            }
            base.Dispose(disposing);
        }

        #region Windows Form Designer generated code

        /// <summary>
        /// Required method for Designer support - do not modify
        /// the contents of this method with the code editor.
        /// </summary>
        private void InitializeComponent()
        {
            System.ComponentModel.ComponentResourceManager resources = new System.ComponentModel.ComponentResourceManager(typeof(TicketEntry));
            this.UploadFileBrowser = new System.Windows.Forms.OpenFileDialog();
            this.BrowseToUploadFile = new System.Windows.Forms.Button();
            this.GoUpload = new System.Windows.Forms.Button();
            this.BrowseToTickets = new System.Windows.Forms.Button();
            this.TicketBrowser = new System.Windows.Forms.FolderBrowserDialog();
            this.OnTicket = new System.Windows.Forms.GroupBox();
            this.SelectedCount = new System.Windows.Forms.TextBox();
            this.GoViewReceipts = new System.Windows.Forms.Button();
            this.GoClearUploads = new System.Windows.Forms.Button();
            this.upFileList = new System.Windows.Forms.ListBox();
            this.GoRetry = new System.Windows.Forms.Button();
            this.ProfileInstructions = new System.Windows.Forms.TextBox();
            this.GoCancel = new System.Windows.Forms.Button();
            this.GoClose = new System.Windows.Forms.Button();
            this.TicketList = new System.Windows.Forms.ListBox();
            this.alcw_ico = new System.Windows.Forms.Panel();
            this.alcw_title = new System.Windows.Forms.Label();
            this.UpProgress = new System.Windows.Forms.ProgressBar();
            this.StatusBox = new System.Windows.Forms.TextBox();
            this.OnTicket.SuspendLayout();
            this.SuspendLayout();
            // 
            // UploadFileBrowser
            // 
            this.UploadFileBrowser.FileName = "*.*";
            this.UploadFileBrowser.Filter = "All Files (*.*)|*.*";
            this.UploadFileBrowser.Multiselect = true;
            this.UploadFileBrowser.Title = "Please select a file to upload";
            // 
            // BrowseToUploadFile
            // 
            this.BrowseToUploadFile.Anchor = ((System.Windows.Forms.AnchorStyles)((System.Windows.Forms.AnchorStyles.Bottom | System.Windows.Forms.AnchorStyles.Right)));
            this.BrowseToUploadFile.Location = new System.Drawing.Point(6, 118);
            this.BrowseToUploadFile.Name = "BrowseToUploadFile";
            this.BrowseToUploadFile.Size = new System.Drawing.Size(133, 29);
            this.BrowseToUploadFile.TabIndex = 4;
            this.BrowseToUploadFile.Text = "Browse to Upload(s)...";
            this.BrowseToUploadFile.UseVisualStyleBackColor = true;
            this.BrowseToUploadFile.Click += new System.EventHandler(this.BrowseToUploadFile_Click);
            // 
            // GoUpload
            // 
            this.GoUpload.Anchor = ((System.Windows.Forms.AnchorStyles)((System.Windows.Forms.AnchorStyles.Bottom | System.Windows.Forms.AnchorStyles.Right)));
            this.GoUpload.Location = new System.Drawing.Point(354, 241);
            this.GoUpload.Name = "GoUpload";
            this.GoUpload.Size = new System.Drawing.Size(76, 23);
            this.GoUpload.TabIndex = 5;
            this.GoUpload.Text = "Upload";
            this.GoUpload.UseVisualStyleBackColor = true;
            this.GoUpload.Visible = false;
            this.GoUpload.Click += new System.EventHandler(this.GoUpload_Click);
            // 
            // BrowseToTickets
            // 
            this.BrowseToTickets.Anchor = ((System.Windows.Forms.AnchorStyles)((System.Windows.Forms.AnchorStyles.Bottom | System.Windows.Forms.AnchorStyles.Right)));
            this.BrowseToTickets.Location = new System.Drawing.Point(392, 177);
            this.BrowseToTickets.Name = "BrowseToTickets";
            this.BrowseToTickets.Size = new System.Drawing.Size(56, 23);
            this.BrowseToTickets.TabIndex = 8;
            this.BrowseToTickets.Text = "Browse";
            this.BrowseToTickets.UseVisualStyleBackColor = true;
            this.BrowseToTickets.Click += new System.EventHandler(this.BrowseToTickets_Click);
            // 
            // OnTicket
            // 
            this.OnTicket.Anchor = ((System.Windows.Forms.AnchorStyles)(((System.Windows.Forms.AnchorStyles.Bottom | System.Windows.Forms.AnchorStyles.Left)
                        | System.Windows.Forms.AnchorStyles.Right)));
            this.OnTicket.Controls.Add(this.SelectedCount);
            this.OnTicket.Controls.Add(this.GoViewReceipts);
            this.OnTicket.Controls.Add(this.GoClearUploads);
            this.OnTicket.Controls.Add(this.upFileList);
            this.OnTicket.Controls.Add(this.GoUpload);
            this.OnTicket.Controls.Add(this.GoRetry);
            this.OnTicket.Controls.Add(this.ProfileInstructions);
            this.OnTicket.Controls.Add(this.GoCancel);
            this.OnTicket.Controls.Add(this.BrowseToUploadFile);
            this.OnTicket.Enabled = false;
            this.OnTicket.Location = new System.Drawing.Point(12, 201);
            this.OnTicket.Name = "OnTicket";
            this.OnTicket.Size = new System.Drawing.Size(436, 270);
            this.OnTicket.TabIndex = 9;
            this.OnTicket.TabStop = false;
            this.OnTicket.Text = "Please browse for or select a ticket";
            // 
            // SelectedCount
            // 
            this.SelectedCount.Location = new System.Drawing.Point(146, 126);
            this.SelectedCount.Name = "SelectedCount";
            this.SelectedCount.ReadOnly = true;
            this.SelectedCount.Size = new System.Drawing.Size(284, 20);
            this.SelectedCount.TabIndex = 13;
            this.SelectedCount.Visible = false;
            // 
            // GoViewReceipts
            // 
            this.GoViewReceipts.Location = new System.Drawing.Point(172, 241);
            this.GoViewReceipts.Name = "GoViewReceipts";
            this.GoViewReceipts.Size = new System.Drawing.Size(75, 23);
            this.GoViewReceipts.TabIndex = 12;
            this.GoViewReceipts.Text = "Receipts";
            this.GoViewReceipts.UseVisualStyleBackColor = true;
            this.GoViewReceipts.Visible = false;
            this.GoViewReceipts.Click += new System.EventHandler(this.GoViewReceipts_Click);
            // 
            // GoClearUploads
            // 
            this.GoClearUploads.Anchor = ((System.Windows.Forms.AnchorStyles)((System.Windows.Forms.AnchorStyles.Bottom | System.Windows.Forms.AnchorStyles.Right)));
            this.GoClearUploads.Location = new System.Drawing.Point(273, 241);
            this.GoClearUploads.Name = "GoClearUploads";
            this.GoClearUploads.Size = new System.Drawing.Size(75, 23);
            this.GoClearUploads.TabIndex = 11;
            this.GoClearUploads.Text = "Clear";
            this.GoClearUploads.UseVisualStyleBackColor = true;
            this.GoClearUploads.Visible = false;
            this.GoClearUploads.Click += new System.EventHandler(this.GoClearUploads_Click);
            // 
            // upFileList
            // 
            this.upFileList.Anchor = ((System.Windows.Forms.AnchorStyles)(((System.Windows.Forms.AnchorStyles.Bottom | System.Windows.Forms.AnchorStyles.Left)
                        | System.Windows.Forms.AnchorStyles.Right)));
            this.upFileList.FormattingEnabled = true;
            this.upFileList.Location = new System.Drawing.Point(9, 149);
            this.upFileList.Name = "upFileList";
            this.upFileList.Size = new System.Drawing.Size(424, 82);
            this.upFileList.TabIndex = 10;
            // 
            // GoRetry
            // 
            this.GoRetry.Anchor = ((System.Windows.Forms.AnchorStyles)((System.Windows.Forms.AnchorStyles.Bottom | System.Windows.Forms.AnchorStyles.Left)));
            this.GoRetry.Location = new System.Drawing.Point(90, 241);
            this.GoRetry.Name = "GoRetry";
            this.GoRetry.Size = new System.Drawing.Size(75, 23);
            this.GoRetry.TabIndex = 7;
            this.GoRetry.Text = "Retry";
            this.GoRetry.UseVisualStyleBackColor = true;
            this.GoRetry.Visible = false;
            this.GoRetry.Click += new System.EventHandler(this.GoRetry_Click);
            // 
            // ProfileInstructions
            // 
            this.ProfileInstructions.Anchor = ((System.Windows.Forms.AnchorStyles)((((System.Windows.Forms.AnchorStyles.Top | System.Windows.Forms.AnchorStyles.Bottom)
                        | System.Windows.Forms.AnchorStyles.Left)
                        | System.Windows.Forms.AnchorStyles.Right)));
            this.ProfileInstructions.Location = new System.Drawing.Point(9, 19);
            this.ProfileInstructions.Multiline = true;
            this.ProfileInstructions.Name = "ProfileInstructions";
            this.ProfileInstructions.ScrollBars = System.Windows.Forms.ScrollBars.Vertical;
            this.ProfileInstructions.Size = new System.Drawing.Size(421, 93);
            this.ProfileInstructions.TabIndex = 0;
            // 
            // GoCancel
            // 
            this.GoCancel.Anchor = ((System.Windows.Forms.AnchorStyles)((System.Windows.Forms.AnchorStyles.Bottom | System.Windows.Forms.AnchorStyles.Left)));
            this.GoCancel.Location = new System.Drawing.Point(9, 241);
            this.GoCancel.Name = "GoCancel";
            this.GoCancel.Size = new System.Drawing.Size(75, 23);
            this.GoCancel.TabIndex = 9;
            this.GoCancel.Text = "Cancel";
            this.GoCancel.UseVisualStyleBackColor = true;
            this.GoCancel.Click += new System.EventHandler(this.GoCancel_Click);
            // 
            // GoClose
            // 
            this.GoClose.Anchor = ((System.Windows.Forms.AnchorStyles)((System.Windows.Forms.AnchorStyles.Bottom | System.Windows.Forms.AnchorStyles.Right)));
            this.GoClose.Location = new System.Drawing.Point(367, 477);
            this.GoClose.Name = "GoClose";
            this.GoClose.Size = new System.Drawing.Size(75, 23);
            this.GoClose.TabIndex = 8;
            this.GoClose.Text = "Close";
            this.GoClose.UseVisualStyleBackColor = true;
            this.GoClose.Click += new System.EventHandler(this.GoClose_Click);
            // 
            // TicketList
            // 
            this.TicketList.Anchor = ((System.Windows.Forms.AnchorStyles)((((System.Windows.Forms.AnchorStyles.Top | System.Windows.Forms.AnchorStyles.Bottom)
                        | System.Windows.Forms.AnchorStyles.Left)
                        | System.Windows.Forms.AnchorStyles.Right)));
            this.TicketList.FormattingEnabled = true;
            this.TicketList.Location = new System.Drawing.Point(12, 50);
            this.TicketList.Name = "TicketList";
            this.TicketList.Size = new System.Drawing.Size(436, 121);
            this.TicketList.TabIndex = 10;
            this.TicketList.SelectedIndexChanged += new System.EventHandler(this.TicketList_SelectedIndexChanged);
            // 
            // alcw_ico
            // 
            this.alcw_ico.BackgroundImage = ((System.Drawing.Image)(resources.GetObject("alcw_ico.BackgroundImage")));
            this.alcw_ico.BackgroundImageLayout = System.Windows.Forms.ImageLayout.None;
            this.alcw_ico.Location = new System.Drawing.Point(12, 12);
            this.alcw_ico.Name = "alcw_ico";
            this.alcw_ico.Size = new System.Drawing.Size(32, 32);
            this.alcw_ico.TabIndex = 11;
            // 
            // alcw_title
            // 
            this.alcw_title.Anchor = ((System.Windows.Forms.AnchorStyles)(((System.Windows.Forms.AnchorStyles.Top | System.Windows.Forms.AnchorStyles.Left)
                        | System.Windows.Forms.AnchorStyles.Right)));
            this.alcw_title.AutoSize = true;
            this.alcw_title.Font = new System.Drawing.Font("Microsoft Sans Serif", 12F, System.Drawing.FontStyle.Regular, System.Drawing.GraphicsUnit.Point, ((byte)(0)));
            this.alcw_title.ForeColor = System.Drawing.SystemColors.ControlText;
            this.alcw_title.Location = new System.Drawing.Point(50, 19);
            this.alcw_title.Name = "alcw_title";
            this.alcw_title.Size = new System.Drawing.Size(239, 20);
            this.alcw_title.TabIndex = 12;
            this.alcw_title.Text = "ALCW Warehouse Management";
            // 
            // UpProgress
            // 
            this.UpProgress.Location = new System.Drawing.Point(12, 477);
            this.UpProgress.Name = "UpProgress";
            this.UpProgress.Size = new System.Drawing.Size(348, 23);
            this.UpProgress.TabIndex = 13;
            this.UpProgress.Visible = false;
            // 
            // StatusBox
            // 
            this.StatusBox.Location = new System.Drawing.Point(13, 477);
            this.StatusBox.Name = "StatusBox";
            this.StatusBox.Size = new System.Drawing.Size(347, 20);
            this.StatusBox.TabIndex = 14;
            this.StatusBox.Visible = false;
            // 
            // TicketEntry
            // 
            this.AutoScaleDimensions = new System.Drawing.SizeF(6F, 13F);
            this.AutoScaleMode = System.Windows.Forms.AutoScaleMode.Font;
            this.ClientSize = new System.Drawing.Size(457, 509);
            this.Controls.Add(this.StatusBox);
            this.Controls.Add(this.UpProgress);
            this.Controls.Add(this.GoClose);
            this.Controls.Add(this.alcw_title);
            this.Controls.Add(this.alcw_ico);
            this.Controls.Add(this.OnTicket);
            this.Controls.Add(this.TicketList);
            this.Controls.Add(this.BrowseToTickets);
            this.Icon = ((System.Drawing.Icon)(resources.GetObject("$this.Icon")));
            this.Name = "TicketEntry";
            this.StartPosition = System.Windows.Forms.FormStartPosition.CenterScreen;
            this.Text = "ALCW Upload by Ticket";
            this.OnTicket.ResumeLayout(false);
            this.OnTicket.PerformLayout();
            this.ResumeLayout(false);
            this.PerformLayout();

        }

        #endregion

        private System.Windows.Forms.OpenFileDialog UploadFileBrowser;
        private System.Windows.Forms.Button BrowseToUploadFile;
        private System.Windows.Forms.Button GoUpload;
        private System.Windows.Forms.Button BrowseToTickets;
        private System.Windows.Forms.FolderBrowserDialog TicketBrowser;
        private System.Windows.Forms.GroupBox OnTicket;
        private System.Windows.Forms.TextBox ProfileInstructions;
        private System.Windows.Forms.Button GoRetry;
        private System.Windows.Forms.Button GoCancel;
        private System.Windows.Forms.ListBox TicketList;
        private System.Windows.Forms.Button GoClose;
        private System.Windows.Forms.Panel alcw_ico;
        private System.Windows.Forms.Label alcw_title;
        private System.Windows.Forms.ProgressBar UpProgress;
        private System.Windows.Forms.ListBox upFileList;
        private System.Windows.Forms.Button GoClearUploads;
        private System.Windows.Forms.TextBox StatusBox;
        private System.Windows.Forms.Button GoViewReceipts;
        private System.Windows.Forms.TextBox SelectedCount;
    }
}

