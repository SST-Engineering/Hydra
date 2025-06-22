namespace ALCW_Ticket_Runner
{
    partial class receipt
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
            this.ReceiptText = new System.Windows.Forms.TextBox();
            this.ReceiptProfile = new System.Windows.Forms.TextBox();
            this.Retry = new System.Windows.Forms.Button();
            this.SuspendLayout();
            // 
            // ReceiptText
            // 
            this.ReceiptText.Anchor = ((System.Windows.Forms.AnchorStyles)((((System.Windows.Forms.AnchorStyles.Top | System.Windows.Forms.AnchorStyles.Bottom)
                        | System.Windows.Forms.AnchorStyles.Left)
                        | System.Windows.Forms.AnchorStyles.Right)));
            this.ReceiptText.BackColor = System.Drawing.SystemColors.Info;
            this.ReceiptText.Location = new System.Drawing.Point(13, 39);
            this.ReceiptText.Multiline = true;
            this.ReceiptText.Name = "ReceiptText";
            this.ReceiptText.ReadOnly = true;
            this.ReceiptText.ScrollBars = System.Windows.Forms.ScrollBars.Vertical;
            this.ReceiptText.Size = new System.Drawing.Size(259, 211);
            this.ReceiptText.TabIndex = 0;
            // 
            // ReceiptProfile
            // 
            this.ReceiptProfile.Anchor = ((System.Windows.Forms.AnchorStyles)(((System.Windows.Forms.AnchorStyles.Top | System.Windows.Forms.AnchorStyles.Left)
                        | System.Windows.Forms.AnchorStyles.Right)));
            this.ReceiptProfile.BackColor = System.Drawing.SystemColors.Info;
            this.ReceiptProfile.Location = new System.Drawing.Point(13, 13);
            this.ReceiptProfile.Name = "ReceiptProfile";
            this.ReceiptProfile.ReadOnly = true;
            this.ReceiptProfile.Size = new System.Drawing.Size(259, 20);
            this.ReceiptProfile.TabIndex = 1;
            // 
            // Retry
            // 
            this.Retry.Anchor = ((System.Windows.Forms.AnchorStyles)((System.Windows.Forms.AnchorStyles.Bottom | System.Windows.Forms.AnchorStyles.Left)));
            this.Retry.BackColor = System.Drawing.SystemColors.Control;
            this.Retry.Location = new System.Drawing.Point(13, 261);
            this.Retry.Name = "Retry";
            this.Retry.Size = new System.Drawing.Size(83, 23);
            this.Retry.TabIndex = 2;
            this.Retry.Text = "Look Again ...";
            this.Retry.UseVisualStyleBackColor = false;
            this.Retry.Click += new System.EventHandler(this.Retry_Click);
            // 
            // receipt
            // 
            this.AutoScaleDimensions = new System.Drawing.SizeF(6F, 13F);
            this.AutoScaleMode = System.Windows.Forms.AutoScaleMode.Font;
            this.ClientSize = new System.Drawing.Size(284, 296);
            this.Controls.Add(this.Retry);
            this.Controls.Add(this.ReceiptProfile);
            this.Controls.Add(this.ReceiptText);
            this.Name = "receipt";
            this.Text = "Receipt";
            this.ResumeLayout(false);
            this.PerformLayout();

        }

        #endregion

        private System.Windows.Forms.TextBox ReceiptText;
        private System.Windows.Forms.TextBox ReceiptProfile;
        private System.Windows.Forms.Button Retry;
    }
}