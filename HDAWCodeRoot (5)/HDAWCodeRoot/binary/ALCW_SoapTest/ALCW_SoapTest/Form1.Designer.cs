namespace ALCW_SoapTest
{
    partial class Form1
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
            System.ComponentModel.ComponentResourceManager resources = new System.ComponentModel.ComponentResourceManager(typeof(Form1));
            this.alcw_url = new System.Windows.Forms.TextBox();
            this.alcw_url_label = new System.Windows.Forms.Label();
            this.alcw_soap_request = new System.Windows.Forms.TextBox();
            this.alcw_soap_response = new System.Windows.Forms.TextBox();
            this.alcw_soap_go = new System.Windows.Forms.Button();
            this.alcw_soap_error = new System.Windows.Forms.TextBox();
            this.SuspendLayout();
            // 
            // alcw_url
            // 
            this.alcw_url.Location = new System.Drawing.Point(40, 19);
            this.alcw_url.Name = "alcw_url";
            this.alcw_url.Size = new System.Drawing.Size(698, 20);
            this.alcw_url.TabIndex = 0;
            this.alcw_url.Text = "http://localhost/TRUW/TRU_soap_accept.php";
            // 
            // alcw_url_label
            // 
            this.alcw_url_label.AutoSize = true;
            this.alcw_url_label.Location = new System.Drawing.Point(13, 19);
            this.alcw_url_label.Name = "alcw_url_label";
            this.alcw_url_label.Size = new System.Drawing.Size(21, 13);
            this.alcw_url_label.TabIndex = 1;
            this.alcw_url_label.Text = "url:";
            // 
            // alcw_soap_request
            // 
            this.alcw_soap_request.Location = new System.Drawing.Point(40, 46);
            this.alcw_soap_request.Multiline = true;
            this.alcw_soap_request.Name = "alcw_soap_request";
            this.alcw_soap_request.ScrollBars = System.Windows.Forms.ScrollBars.Both;
            this.alcw_soap_request.Size = new System.Drawing.Size(440, 192);
            this.alcw_soap_request.TabIndex = 2;
            this.alcw_soap_request.Text = resources.GetString("alcw_soap_request.Text");
            // 
            // alcw_soap_response
            // 
            this.alcw_soap_response.Location = new System.Drawing.Point(40, 245);
            this.alcw_soap_response.Multiline = true;
            this.alcw_soap_response.Name = "alcw_soap_response";
            this.alcw_soap_response.ScrollBars = System.Windows.Forms.ScrollBars.Both;
            this.alcw_soap_response.Size = new System.Drawing.Size(440, 187);
            this.alcw_soap_response.TabIndex = 3;
            // 
            // alcw_soap_go
            // 
            this.alcw_soap_go.Location = new System.Drawing.Point(744, 19);
            this.alcw_soap_go.Name = "alcw_soap_go";
            this.alcw_soap_go.Size = new System.Drawing.Size(75, 23);
            this.alcw_soap_go.TabIndex = 4;
            this.alcw_soap_go.Text = "Go";
            this.alcw_soap_go.UseVisualStyleBackColor = true;
            this.alcw_soap_go.Click += new System.EventHandler(this.alcw_soap_go_Click);
            // 
            // alcw_soap_error
            // 
            this.alcw_soap_error.Location = new System.Drawing.Point(487, 46);
            this.alcw_soap_error.Multiline = true;
            this.alcw_soap_error.Name = "alcw_soap_error";
            this.alcw_soap_error.ScrollBars = System.Windows.Forms.ScrollBars.Both;
            this.alcw_soap_error.Size = new System.Drawing.Size(348, 386);
            this.alcw_soap_error.TabIndex = 5;
            // 
            // Form1
            // 
            this.AutoScaleDimensions = new System.Drawing.SizeF(6F, 13F);
            this.AutoScaleMode = System.Windows.Forms.AutoScaleMode.Font;
            this.ClientSize = new System.Drawing.Size(944, 484);
            this.Controls.Add(this.alcw_soap_error);
            this.Controls.Add(this.alcw_soap_go);
            this.Controls.Add(this.alcw_soap_response);
            this.Controls.Add(this.alcw_soap_request);
            this.Controls.Add(this.alcw_url_label);
            this.Controls.Add(this.alcw_url);
            this.Name = "Form1";
            this.Text = "Soap Tester";
            this.ResumeLayout(false);
            this.PerformLayout();

        }

        #endregion

        private System.Windows.Forms.TextBox alcw_url;
        private System.Windows.Forms.Label alcw_url_label;
        private System.Windows.Forms.TextBox alcw_soap_request;
        private System.Windows.Forms.TextBox alcw_soap_response;
        private System.Windows.Forms.Button alcw_soap_go;
        private System.Windows.Forms.TextBox alcw_soap_error;
    }
}

