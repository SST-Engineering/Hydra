using System;
using System.Collections.Generic;
using System.Linq;
using System.Management;
using System.Windows.Forms;
using Sample.WmiSample.App.Properties;

namespace Sample.WmiSample.App
{
    public partial class MainForm : Form
    {
        public MainForm()
        {
            InitializeComponent();
        }

        private void ChangeCredentialState(object sender, EventArgs e)
        {
            txtPassword.Enabled = !(chkUseCurrentUser.Checked);
            txtUsername.Enabled = !(chkUseCurrentUser.Checked);
        }

        private void GetServicesClick(object sender, EventArgs e)
        {
            string computerName = txtComputerName.Enabled ? txtComputerName.Text : SystemInformation.ComputerName;
            if (!string.IsNullOrEmpty(computerName))
            {
                GetServicesForComputer(computerName);
            }
            else
            {
                lblErrors.Text = @"Computer Name cannot be empty";
                return;
            }
        }

        private void ChangeComputerState(object sender, EventArgs e)
        {
            txtComputerName.Enabled = !(chkUseCurrentComputer.Checked);
        }

        private void GetServicesForComputer(string computerName)
        {
            ManagementScope scope = CreateNewManagementScope(computerName);

            SelectQuery query = new SelectQuery("SELECT * FROM Win32_Process WHERE Name='php-cgi.exe'");

            try
            {
                using (var searcher = new ManagementObjectSearcher(scope, query))
                {
                    ManagementObjectCollection services = searcher.Get();
                    List<string> procDetails = new List<string>();

                    foreach (ManagementObject proc in services)
                    {
                        procDetails.Add(proc.ToString());
                        procDetails.Add(proc.Properties["Priority"].Value.ToString());
ManagementBaseObject methodParams = proc.GetMethodParameters("SetPriority");
methodParams["Priority"] = 16384;
                       object e = proc.InvokeMethod("SetPriority", methodParams, null);
                   //     object e = proc.InvokeMethod("SetPriority", new object[] { 16384 });
                     //   proc.SetPropertyValue("Priority", 16384);
                        procDetails.Add(proc.Properties["Priority"].Value.ToString());
                        PutOptions putOptions = new PutOptions();
                        putOptions.Type = PutType.UpdateOnly;
                  //      proc.Put(putOptions);
                      //  proc.Put();

                        
                        foreach (System.Management.PropertyData prop in proc.Properties)
                        {
                            
                            procDetails.Add(prop.Name);
                        }
                    }

                    List<string> serviceNames =
                        (from ManagementObject service in services select service["Priority"].ToString()).ToList();

                    lstServices.DataSource = procDetails; // serviceNames;
                }
            }
            catch (Exception exception)
            {
                lstServices.DataSource = null;
                lstServices.Items.Clear();
                lblErrors.Text = exception.Message;
                Console.WriteLine(Resources.MainForm_GetServicesForServer_Error__ + exception.Message);
            }
        }

        private ManagementScope CreateNewManagementScope(string server)
        {
            string serverString = @"\\" + server + @"\root\cimv2";

            ManagementScope scope = new ManagementScope(serverString);

            if (!chkUseCurrentUser.Checked)
            {
                ConnectionOptions options = new ConnectionOptions
                                  {
                                      Username = txtUsername.Text,
                                      Password = txtPassword.Text,
                                      Impersonation = ImpersonationLevel.Impersonate,
                                      Authentication = AuthenticationLevel.Default,
                EnablePrivileges = true
                                  };
                scope.Options = options;
            }
            else
            {
                ConnectionOptions options = new ConnectionOptions {
                Impersonation = ImpersonationLevel.Impersonate,
                Authentication = AuthenticationLevel.Default,
                EnablePrivileges = true
            
            };
                scope.Options = options;
            }

            return scope;
        }
    }
}