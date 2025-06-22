namespace HDACronService
{
    using System;
    using System.ServiceProcess;
    using System.Configuration.Install;

    /// <summary>
    ///     Summary description for ProjectInstaller.
    /// </summary>
    [System.ComponentModel.RunInstaller(true)]
    public class ProjectInstaller : System.Configuration.Install.Installer
    {
        /// <summary>
        ///    Required designer variable.
        /// </summary>
        //private System.ComponentModel.Container components;
        private System.ServiceProcess.ServiceInstaller serviceInstaller;
        private System.ServiceProcess.ServiceProcessInstaller serviceProcessInstaller;

        public ProjectInstaller()
        {
            // This call is required by the Designer.
            InitializeComponent();
        }

        /// <summary>
        ///    Required method for Designer support - do not modify
        ///    the contents of this method with the code editor.
        /// </summary>
        private void InitializeComponent()
        {
            this.serviceInstaller = new System.ServiceProcess.ServiceInstaller();
            this.serviceProcessInstaller = new System.ServiceProcess.ServiceProcessInstaller();
            // 
            // serviceInstaller
            // 
            this.serviceInstaller.Description = "HDA Cron Service Provider";
            this.serviceInstaller.DisplayName = "HDA Cron Service";
            this.serviceInstaller.ServiceName = "HDACronService";
            this.serviceInstaller.StartType = System.ServiceProcess.ServiceStartMode.Automatic;
            // 
            // serviceProcessInstaller
            // 
            this.serviceProcessInstaller.Account = System.ServiceProcess.ServiceAccount.LocalService;
            this.serviceProcessInstaller.Password = null;
            this.serviceProcessInstaller.Username = null;
            // 
            // ServiceInstaller
            // 
            this.Installers.AddRange(new System.Configuration.Install.Installer[] {
            this.serviceProcessInstaller,
            this.serviceInstaller});

            this.Committed += new InstallEventHandler(ServiceInstaller_Committed);


        }
        void ServiceInstaller_Committed(object sender, InstallEventArgs e)
        {
            // Auto Start the Service Once Installation is Finished.
            string strServiceName = this.serviceInstaller.ServiceName;
            var controller = new ServiceController(strServiceName);
            controller.Start();
        }

    }
}