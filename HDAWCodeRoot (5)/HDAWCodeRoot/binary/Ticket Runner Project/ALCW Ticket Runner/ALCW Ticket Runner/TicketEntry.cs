using System;
using System.Collections.Generic;
using System.ComponentModel;
using System.Data;
using System.Drawing;
using System.Linq;
using System.Text;
using System.Text.RegularExpressions;
using System.Windows.Forms;
using System.Net;
using System.Threading;
using System.Security.Cryptography;
using Ionic.Zip;


using System.IO;
using Microsoft.Win32;

namespace ALCW_Ticket_Runner
{
    public partial class TicketEntry : Form
    {
        public TicketEntry()
        {
            InitializeComponent();

            string lastPath;
            lastPath = getLastTicketPath();
            if (Directory.Exists(lastPath))
            {
                getTickets(lastPath);
            }
        }

        private void BrowseToUploadFile_Click(object sender, EventArgs e)
        {
            string lastPath;
            lastPath = getLastDataPath();
            if (Directory.Exists(lastPath))
            {
                this.UploadFileBrowser.InitialDirectory = lastPath;
            }

            DialogResult open = this.UploadFileBrowser.ShowDialog();
            if (open == DialogResult.OK)
            {
                saveLastDataPath();
                foreach (string filename in this.UploadFileBrowser.FileNames)
                {
                    this.upFileList.Items.Add(filename);
                }
                this.GoUpload.Visible = true;
                this.GoClearUploads.Visible = true;
                this.SelectedCount.Text = this.upFileList.Items.Count + " files selected for uploading";
                this.SelectedCount.Visible = true;
            }
        }

        private bool _useAsync = false;
        private void GoUpload_Click(object sender, EventArgs e)
        {
            Cursor.Current = Cursors.WaitCursor;
            this.StatusBox.Visible = false;
            this.UpProgress.Visible = true;
            this.SayStatus("Uploading..." + Environment.NewLine);
            FtpMakeDirectory maker = new FtpMakeDirectory();
            string t = this.ticket.Url + "/" + this.ticket.BaseDir + "/";
            string dt = this.ticket.DateDir;
            if (dt != null)
            {
                t += dt;
                this.SayStatus(maker.run(t, this.ticket.UName, this.ticket.PW)+Environment.NewLine);
                t += "/";
            }
            t += this.ticket.Profile;
            this.SayStatus(maker.run(t, this.ticket.UName, this.ticket.PW)+Environment.NewLine);

            string zipFile = Path.GetTempPath() + "/" + this.ticket.Profile + ".zip";
            ZipFile zip = new ZipFile();
            System.Collections.ArrayList tktfiles = new System.Collections.ArrayList();
            foreach (string filename in this.upFileList.Items)
            {
                Int32 crc = 0;
                byte[] b = File.ReadAllBytes(filename);
                foreach (byte bb in b) crc += bb;
                string scrc = System.Convert.ToString(crc);
                scrc += '|';
                scrc += this.ticket.TicketRef;
                scrc += '|';
                scrc += this.ticket.Profile;
                scrc += '|';
                scrc += this.ticket.User;
                _alcw_decode decode = new _alcw_decode();
                scrc = decode.coded(scrc);
                string tofile = Path.GetFileName(filename);
                string tktfile = Path.GetTempPath() + tofile + ".tkt";
                File.WriteAllText(tktfile, scrc);
                zip.AddFile(filename, "");
                zip.AddFile(tktfile, "");
                tktfiles.Add(tktfile);
            }
            zip.Save(zipFile);
            foreach (string tktfile in tktfiles) File.Delete(tktfile);

            int uploadedBytes = 0;
            if (this._useAsync)
            {
                AsynchronousFtpUpLoader loader = new AsynchronousFtpUpLoader();
                this.SayStatus(loader.run(t + "/" + Path.GetFileName(zipFile), zipFile, this.ticket.UName, this.ticket.PW) + Environment.NewLine);
            }
            else
            {
                SynchronousFtpUpLoader loader = new SynchronousFtpUpLoader(this);
                this.SayStatus(loader.run(t + "/" + Path.GetFileName(zipFile), zipFile, this.ticket.UName, this.ticket.PW)+Environment.NewLine);
                uploadedBytes = loader.bytesWritten;
            }

            zip = null;
            File.Delete(zipFile);
            FtpCheckUpload checker = new FtpCheckUpload();
            string s = checker.run(t + "/" + Path.GetFileName(zipFile), this.ticket.UName, this.ticket.PW, uploadedBytes);
            this.StatusBox.Text += s;
            Cursor.Current = Cursors.Default;

            this.UpProgress.Visible = false;
            this.GoUpload.Visible = false;
            this.GoRetry.Visible = true;
            this.StatusBox.Visible = true;

        }

        private int p_stat = 0;
        public void SayStatus(string s)
        {
            if ((this.p_stat += 10) > 100) this.p_stat = 0;
            this.UpProgress.Value = this.p_stat;
            this.ProfileInstructions.Text += s;
            this.Refresh();
        }
        public void ToStatusBox(string s)
        {
            this.StatusBox.Text = s;
        }

        private void BrowseToTickets_Click(object sender, EventArgs e)
        {
            string lastPath;
            lastPath = getLastTicketPath();
            if (Directory.Exists(lastPath))
            {
                this.TicketBrowser.SelectedPath = lastPath;
            }

            DialogResult open = this.TicketBrowser.ShowDialog();
            if (open == DialogResult.OK)
            {
                saveLastTicketPath();
                getTickets(this.TicketBrowser.SelectedPath);
            }
        }

        private void getTickets(string path)
        {
            this.TicketList.Items.Clear();
            this._clear_reset();
            string[] ff = Directory.GetFiles(path, "*.tkt");
            foreach (string f in ff)
            {
                TicketList.Items.Add(new Ticket(f));
            }
            if (TicketList.Items.Count > 0)
            {
                TicketList.SelectedIndex = 0;
            }
        }

        private string getLastTicketPath()
        {
            string res = "";
            RegistryKey rk;
            rk = Registry.CurrentUser.OpenSubKey(@"SOFTWARE\Excel In Business\ALCW Ticket Runner", false);
            if (rk != null)
            {
                res = Convert.ToString(rk.GetValue("Last Ticket Folder", ""));
                rk.Close();
            }
            return res;
        }
        private void saveLastTicketPath()
        {
            RegistryKey rk;
            rk = Registry.CurrentUser.OpenSubKey(@"SOFTWARE\Excel In Business\ALCW Ticket Runner", true);
            if (rk == null)
            {
                rk = Registry.CurrentUser.CreateSubKey(@"SOFTWARE\Excel In Business\ALCW Ticket Runner");
            }
            rk.SetValue("Last Ticket Folder", this.TicketBrowser.SelectedPath);
            rk.Close();
        }

        private string getLastDataPath()
        {
            string res = "";
            RegistryKey rk;
            rk = Registry.CurrentUser.OpenSubKey(@"SOFTWARE\Excel In Business\ALCW Ticket Runner", false);
            if (rk != null)
            {
                res = Convert.ToString(rk.GetValue("Last Data Folder", ""));
                rk.Close();
            }
            return res;
        }
        private void saveLastDataPath()
        {
            RegistryKey rk;
            rk = Registry.CurrentUser.OpenSubKey(@"SOFTWARE\Excel In Business\ALCW Ticket Runner", true);
            if (rk == null)
            {
                rk = Registry.CurrentUser.CreateSubKey(@"SOFTWARE\Excel In Business\ALCW Ticket Runner");
            }
            if (this.UploadFileBrowser.FileNames.Length > 0)
            {
                string dir = Path.GetDirectoryName(this.UploadFileBrowser.FileNames[0]);
                rk.SetValue("Last Data Folder", dir);
            }
            rk.Close();
        }

        private Ticket ticket = null;

        private void UploadFileName_TextChanged(object sender, EventArgs e)
        {
            this.GoUpload.Visible = true;
        }

        private void GoRetry_Click(object sender, EventArgs e)
        {
            if (this.ticket != null)
            {
                this.ProfileInstructions.Text = "Retry Upload.." + Environment.NewLine + this.ticket.Instructions;
                GoUpload_Click(sender, e);
            }
        }

        private void GoCancel_Click(object sender, EventArgs e)
        {
            this._clear_reset();
        }
        private void _clear_reset()
        {
            this.OnTicket.Enabled = false;
            this.GoRetry.Visible = false;
            this.GoUpload.Visible = false;
            this.ProfileInstructions.Text = "";
            this.UpProgress.Visible = false;
            this.upFileList.Items.Clear();
            this.TicketList.ClearSelected();
        }

        private void GoClose_Click(object sender, EventArgs e)
        {
            Application.Exit();
        }

        private void TicketList_SelectedIndexChanged(object sender, EventArgs e)
        {
            ListBox chk = (ListBox)sender;
            if (chk.SelectedItem != null)
            {
                this.ticket = (Ticket)((ListBox)sender).SelectedItem;
                this.OnTicket.Text = this.ticket.ToString();
                this.OnTicket.Enabled = true;
                this.GoRetry.Visible = false;
                this.GoViewReceipts.Visible = true;
                this.ProfileInstructions.Text = this.ticket.Instructions;
                this.upFileList.Items.Clear();
            }
            else
            {
                this.OnTicket.Text = "";
                this.GoRetry.Visible = false;
                this.OnTicket.Enabled = false;
                this.GoViewReceipts.Visible = false;
                this.ticket = null;
                this.upFileList.Items.Clear();
            }

        }

        private void GoClearUploads_Click(object sender, EventArgs e)
        {
            this.upFileList.Items.Clear();
            this.GoClearUploads.Visible = false;
        }

        private void GoViewReceipts_Click(object sender, EventArgs e)
        {
            receipt receipt = new receipt();
            receipt.run(this.ticket);
        }

    }

    public class Ticket
    {
        private string filepath;
        public Ticket(string filepath)
        {
            this.filepath = filepath;
            this._profileName = Path.GetFileNameWithoutExtension(this.filepath);
            string s = File.ReadAllText(this.filepath);
            _alcw_decode crpt = new _alcw_decode();
            s = crpt.coded(s);
            StringReader sr = new StringReader(s);

            this._vn = sr.ReadLine();
            this._ticket = sr.ReadLine();
            this._profile = sr.ReadLine();
            this._profiledir = sr.ReadLine();
            this._user = sr.ReadLine();
            this._url = sr.ReadLine();
            this._uname = sr.ReadLine();
            this._pw = sr.ReadLine();
            this._basedir = sr.ReadLine();
            this._datedir = sr.ReadLine();
            this._instructions = sr.ReadToEnd();
            sr.Close();
        }
        private string _vn;
        private string _user;
        private string _profileName;
        private string _profiledir;
        override public string ToString()
        {
            return this._profileName;
        }
        private string _instructions;
        private string _url;
        private string _uname;
        private string _pw;
        private string _profile;
        private string _ticket;
        private string _basedir;
        private string _datedir;
        public string Instructions
        {
            get
            {
                Regex rx = new Regex("INSTR>(?<instr>.*?)<INSTR", RegexOptions.IgnoreCase | RegexOptions.Singleline);
                Match m = rx.Match(this._instructions);
                if (m.Success)
                {
                    return m.Groups["instr"].Value;
                }

                return _instructions;
            }
        }
        public string Url { get { return _url; } }
        public string UName { get { return _uname; } }
        public string PW { get { return _pw; } }
        public string Profile { get { return _profiledir; } }
        public string BaseDir { get { return _basedir; } }
        public string DateDir
        {
            get
            {
                return (_datedir == "0") ? null : "D" + DateTime.Today.ToString("yyyyMMdd");
            }
        }
        public string TicketRef { get { return this._ticket; } }
        public string User { get { return this._user; } }

    }
    public class FtpState
    {
        private ManualResetEvent wait;
        private FtpWebRequest request;
        private string fileName;
        private Exception operationException = null;
        string status;

        public FtpState()
        {
            wait = new ManualResetEvent(false);
        }

        public ManualResetEvent OperationComplete
        {
            get { return wait; }
        }

        public FtpWebRequest Request
        {
            get { return request; }
            set { request = value; }
        }

        public string FileName
        {
            get { return fileName; }
            set { fileName = value; }
        }
        public Exception OperationException
        {
            get { return operationException; }
            set { operationException = value; }
        }
        public string StatusDescription
        {
            get { return status; }
            set { status = value; }
        }
    }
    public class SynchronousFtpUpLoader
    {
        private TicketEntry caller;
        public SynchronousFtpUpLoader(TicketEntry _caller)
        {
            this.caller = _caller;
        }
        public int bytesWritten = 0;
        public string run(string _target, string _filename, string _auth, string _authpw)
        {
            Uri target = new Uri("ftp://" + _target);
            string fileName = _filename;
            FtpWebRequest request = (FtpWebRequest)WebRequest.Create(target);
            request.Method = WebRequestMethods.Ftp.UploadFile;
            request.Credentials = new NetworkCredential(_auth, _authpw);
            try
            {
                FtpWebResponse response = (FtpWebResponse)request.GetResponse();
                Stream requestStream = request.GetRequestStream();
                caller.SayStatus("Connected..\n");
                // Copy the file contents to the request stream. 
                const int bufferLength = 2048;
                byte[] buffer = new byte[bufferLength];
                int readBytes = 0;
                FileStream stream = File.OpenRead(_filename);
                do
                {
                    readBytes = stream.Read(buffer, 0, bufferLength);
                    requestStream.Write(buffer, 0, readBytes);
                    this.bytesWritten += readBytes;
                    caller.SayStatus("..");
                }
                while (readBytes != 0);
                stream.Close();
                caller.SayStatus("Written " + this.bytesWritten + " bytes\n");
                requestStream.Close();
                string s = response.StatusDescription;
                response.Close();
                this.caller.ToStatusBox("Says No Error ");
                return s;
            }
            
            catch (Exception e)
            {
                return "Could not get the request stream. "+e.Message;
            }

        }

       
    }
    public class AsynchronousFtpUpLoader
    {
        // Command line arguments are two strings: 
        // 1. The url that is the name of the file being uploaded to the server. 
        // 2. The name of the file on the local machine. 
        // 
        public AsynchronousFtpUpLoader()
        {
        }
        public string run(string _target, string _filename, string _auth, string _authpw)
        {
            // Create a Uri instance with the specified URI string. 
            // If the URI is not correctly formed, the Uri constructor 
            // will throw an exception.
            ManualResetEvent waitObject;

            Uri target = new Uri("ftp://" + _target);
            string fileName = _filename;
            FtpState state = new FtpState();
            FtpWebRequest request = (FtpWebRequest)WebRequest.Create(target);
            request.Method = WebRequestMethods.Ftp.UploadFile;

            // This example uses anonymous logon. 
            // The request is anonymous by default; the credential does not have to be specified.  
            // The example specifies the credential only to 
            // control how actions are logged on the server.

            request.Credentials = new NetworkCredential(_auth, _authpw);

            // Store the request in the object that we pass into the 
            // asynchronous operations.
            state.Request = request;
            state.FileName = fileName;

            // Get the event to wait on.
            waitObject = state.OperationComplete;

            // Asynchronously get the stream for the file contents.
            request.BeginGetRequestStream(
                new AsyncCallback(EndGetStreamCallback),
                state
            );

            // Block the current thread until all operations are complete.
            waitObject.WaitOne();

            // The operations either completed or threw an exception. 
            if (state.OperationException != null)
            {
                return state.OperationException.Message;
            }
            else
            {
                return "The operation completed - " + state.StatusDescription;
            }
        }
        private static void EndGetStreamCallback(IAsyncResult ar)
        {
            FtpState state = (FtpState)ar.AsyncState;

            Stream requestStream = null;
            // End the asynchronous call to get the request stream. 
            try
            {
                requestStream = state.Request.EndGetRequestStream(ar);
                // Copy the file contents to the request stream. 
                const int bufferLength = 2048;
                byte[] buffer = new byte[bufferLength];
                int count = 0;
                int readBytes = 0;
                FileStream stream = File.OpenRead(state.FileName);
                do
                {
                    readBytes = stream.Read(buffer, 0, bufferLength);
                    requestStream.Write(buffer, 0, readBytes);
                    count += readBytes;
                }
                while (readBytes != 0);
                Console.WriteLine("Writing {0} bytes to the stream.", count);
                // IMPORTANT: Close the request stream before sending the request.
                requestStream.Close();
                // Asynchronously get the response to the upload request.
                state.Request.BeginGetResponse(
                    new AsyncCallback(EndGetResponseCallback),
                    state
                );
            }
            // Return exceptions to the main application thread. 
            catch (Exception e)
            {
                Console.WriteLine("Could not get the request stream.");
                state.OperationException = e;
                state.OperationComplete.Set();
                return;
            }

        }

        // The EndGetResponseCallback method   
        // completes a call to BeginGetResponse. 
        private static void EndGetResponseCallback(IAsyncResult ar)
        {
            FtpState state = (FtpState)ar.AsyncState;
            FtpWebResponse response = null;
            try
            {
                response = (FtpWebResponse)state.Request.EndGetResponse(ar);
                response.Close();
                state.StatusDescription = response.StatusDescription;
                // Signal the main application thread that  
                // the operation is complete.
                state.OperationComplete.Set();
            }
            // Return exceptions to the main application thread. 
            catch (Exception e)
            {
                Console.WriteLine("Error getting response.");
                state.OperationException = e;
                state.OperationComplete.Set();
            }
        }
    }

    public class FtpMakeDirectory
    {
        public FtpMakeDirectory()
        {
        }
        public string run(string _target, string _auth, string _authpw)
        {
            Uri target = new Uri("ftp://" + _target);
            try
            {
                FtpWebRequest request_make = (FtpWebRequest)WebRequest.Create(target);
                request_make.Method = WebRequestMethods.Ftp.MakeDirectory;
                request_make.Credentials = new NetworkCredential(_auth, _authpw);
                FtpWebResponse response_make = (FtpWebResponse)request_make.GetResponse();
                response_make.Close();
           /*     FtpWebRequest request_exists = (FtpWebRequest)WebRequest.Create(target);
                request_exists.Credentials = new NetworkCredential(_auth, _authpw);
                request_exists.Method = WebRequestMethods.Ftp.ListDirectory;
                FtpWebResponse response_exists = (FtpWebResponse)request_exists.GetResponse();
                response_exists.Close();
                return "Directory for profile: " + target + " already exists" + Environment.NewLine;*/
                return "Directory for profile: " + target + " created" + Environment.NewLine;;
            }
            catch (WebException wex)
            {
                FtpWebResponse response = (FtpWebResponse)wex.Response;
                if (response.StatusCode == FtpStatusCode.ActionNotTakenFileUnavailable)
                {
           /*         FtpWebRequest request_make = (FtpWebRequest)WebRequest.Create(target);
                    request_make.Method = WebRequestMethods.Ftp.MakeDirectory;
                    request_make.Credentials = new NetworkCredential(_auth, _authpw);
                    FtpWebResponse response_make = (FtpWebResponse)request_make.GetResponse();
                    response_make.Close();*/
            //        return "Making directory for profile: " + response_make.StatusDescription + Environment.NewLine;
                    return "Directory for profile: " + target + " already exists" + Environment.NewLine;
                }
                return "Fails to test directory " + target + " other error " + response.ExitMessage + Environment.NewLine;
            }
        }
    }
    public class FtpCheckUpload
    {
        public FtpCheckUpload()
        {
        }
        public string run(string _target, string _auth, string _authpw, int _size)
        {
            Uri target = new Uri("ftp://" + _target);
            try
            {
                FtpWebRequest request_exists = (FtpWebRequest)WebRequest.Create(target);
                request_exists.Credentials = new NetworkCredential(_auth, _authpw);
                request_exists.Method = WebRequestMethods.Ftp.GetFileSize;
                FtpWebResponse response_exists = (FtpWebResponse)request_exists.GetResponse();
                long sz = response_exists.ContentLength;
                response_exists.Close();
                if (sz == _size) 
                    return "Uploaded file size and bytes written match, " + System.Convert.ToString(sz)+" bytes";
                else
                    return "File size: " + System.Convert.ToString(sz) + " bytes, Expected: " + System.Convert.ToString(_size) + " bytes";
            }
            catch (WebException wex)
            {
                FtpWebResponse response = (FtpWebResponse)wex.Response;
                if (response.StatusCode == FtpStatusCode.ActionNotTakenFileUnavailable)
                {
                    return "File Not available "+wex.Message;
                }
                return "Fails to test file exists "+wex.Message;
            }
        }
    }
    public class FtpGetReceipt
    {
        public FtpGetReceipt()
        {
        }
        public string run(string _target, string _auth, string _authpw)
        {
            Uri target = new Uri("ftp://" + _target);
            try
            {
                FtpWebRequest receipt = (FtpWebRequest)WebRequest.Create(target);
                receipt.Credentials = new NetworkCredential(_auth, _authpw);
                receipt.Method = WebRequestMethods.Ftp.DownloadFile;
                FtpWebResponse response_receipt = (FtpWebResponse)receipt.GetResponse();
                Stream stream = response_receipt.GetResponseStream();
                const int bufferLength = 2048;
                byte[] buffer = new byte[bufferLength];
                int rBytes = 0;
                string s = "";
                while ((rBytes = stream.Read(buffer, 0, 2048))>0) {
                    s += System.Text.Encoding.UTF8.GetString(buffer);
                }
                response_receipt.Close();
                return s;
            }
            catch (WebException wex)
            {
                FtpWebResponse response = (FtpWebResponse)wex.Response;
                if (response.StatusCode == FtpStatusCode.ActionNotTakenFileUnavailable)
                {
                    return "Receipt Not available";
                }
                return "Fails to collect receipts";
            }
        }
    }
    public class FtpWriteTicket
    {
        public FtpWriteTicket()
        {
        }
        public string run(string _target, string _ticket, string _auth, string _authpw)
        {
            Uri target = new Uri("ftp://" + _target);
            FtpWebRequest request = (FtpWebRequest)WebRequest.Create(target);
            request.Method = WebRequestMethods.Ftp.UploadFile;
            request.Credentials = new NetworkCredential(_auth, _authpw);
            Stream sw = request.GetRequestStream();
            byte[] b = System.Text.Encoding.UTF8.GetBytes(_ticket.ToCharArray());
            sw.Write(b, 0, b.Length);
            sw.Close();
            FtpWebResponse response = (FtpWebResponse)request.GetResponse();
            string s = "Writing ticket check file for profile: " + response.StatusDescription + Environment.NewLine;
            response.Close();
            return s;
        }
    }

    public class _alcw_decode
    {
        public _alcw_decode()
        {
        }
        private string ky = "TheCaseIsAltered";
        internal string coded(string str)
        {
            int kl = this.ky.Length;
            int[] k = new int[kl];
            for (int i = 0; i < kl; i++)
            {
                k[i] = System.Convert.ToInt32(this.ky[i]) & 0x1f;

            }
            int j = 0;
            string s = "";
            byte[] b = System.Text.Encoding.UTF8.GetBytes(str.ToCharArray());
            for (int i = 0; i < b.Length; i++)
            {
                int e = System.Convert.ToInt32(b[i]);
                s += ((e & 0xe0) != 0) ? System.Convert.ToChar(e ^ k[j]) : System.Convert.ToChar(e);
                j++;
                j = (j == kl) ? 0 : j;
            }
            return s;
        }
    }

}
