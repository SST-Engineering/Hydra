namespace HDACronService
{
    using System;
    using System.Collections;
    using System.Threading;
    using System.IO;
    using System.Net;
    using System.Text;
    using System.Configuration.Install;
    using System.ServiceProcess;


    class HDACronService : System.ServiceProcess.ServiceBase
    {
        // The main entry point for the process
        static void Main()
        {
            System.ServiceProcess.ServiceBase[] ServicesToRun;
            ServicesToRun = new System.ServiceProcess.ServiceBase[] { new HDACronService() };
            System.ServiceProcess.ServiceBase.Run(ServicesToRun);
        }
        /// <summary>
        /// Required method for Designer support - do not modify
        /// the contents of this method with the code editor.
        /// </summary>
        private void InitializeComponent()
        {
            this.ServiceName = "HDACronService";
        }
        /// <summary>
        /// Set things in motion so your service can do its work.
        /// </summary>
        private bool hda_inwork = false;
        Thread hda_cron_thread;
        protected override void OnStart(string[] args)
        {
            System.IO.Directory.SetCurrentDirectory(System.AppDomain.CurrentDomain.BaseDirectory);
            CronConfig();
            hdaLog(" HDA Cron Service: Service Started at " + DateTime.Now.ToShortDateString() + " " + DateTime.Now.ToShortTimeString() + "\n");
            ThreadStart st = new ThreadStart(hdaCron);
            hda_cron_thread = new Thread(st);
            hda_inwork = true;
            hda_cron_thread.Start();
        }
        /// <summary>
        /// Stop this service.
        /// </summary>
        protected override void OnStop()
        {
            hda_inwork = false;
            hda_cron_thread.Join(new TimeSpan(0, 1, 0));
            hdaLog(" HDA Cron Service: Service Stopped at " + DateTime.Now.ToShortDateString() + " " + DateTime.Now.ToShortTimeString() + "\n");
        }
        private void hdaCron()
        {
            System.IO.Directory.SetCurrentDirectory(System.AppDomain.CurrentDomain.BaseDirectory);

            while (hda_inwork)
            {
                hdaLog(" HDA Cron Service: Working " + DateTime.Now.ToShortDateString() + " " + DateTime.Now.ToShortTimeString() + "\n");
                TimeSpan tod = DateTime.Now.TimeOfDay;
                if (tod.Hours == 0 && tod.Minutes < 15) Thread.Sleep(new TimeSpan(0, 15-tod.Minutes, 0));
                    StringBuilder sb = new StringBuilder();
                foreach (dUrl toUrl in CronUrl)
                {
                    string toUrlCall = toUrl.url;
                    if (toUrl.toStart) toUrlCall += "?OnStart";
                    hdaLog(" Requesting " + toUrlCall);


                    // used on each read operation
                    byte[] buf = new byte[8192];

                    // prepare the web page we will be asking for
                    try
                    {
                        HttpWebRequest request = (HttpWebRequest)
                            WebRequest.Create(toUrlCall);
                        request.Method = "POST";
                        // execute the request
                        // Set the content length
                        ASCIIEncoding encoding = new ASCIIEncoding();
                        byte[] byteArray = encoding.GetBytes("");
                        request.ContentLength = byteArray.Length; 
                        using (Stream requestStream = request.GetRequestStream())
                        {
                            // Write the "POST" data to the stream
                            requestStream.Write(byteArray, 0, byteArray.Length);
                        }

                        // now put the get response code in a new thread and immediately return
                        ThreadPool.QueueUserWorkItem((x) =>
                        {
                            using (var objResponse = (HttpWebResponse)request.GetResponse())
                            {
                                MemoryStream responseStream = new MemoryStream();
                                objResponse.GetResponseStream().CopyTo(responseStream);
                                responseStream.Seek(0, SeekOrigin.Begin);
                            }
                        });
                        /*
                        HttpWebResponse response = (HttpWebResponse)
                            request.GetResponse();
                        if (response.StatusCode == HttpStatusCode.OK) toUrl.toStart = false;
                         * */
                        toUrl.toStart = false;
                    }
                    catch (Exception e)
                    {
                        sb.AppendLine("HDA Cron fails " + e.Message);
                    }

                    // print out page source
                    if (hda_ToLog)
                    {
                        hdaLog(HDACronService.StripHTML(sb.ToString()));
                    }
                    if (hda_inwork) Thread.Sleep(new TimeSpan(0, frequency, 0));
                }
            }
            Thread.CurrentThread.Abort();
        }
        private void hdaLog(string s)
        {
            if (hda_ToLog)
            {
                TextWriter tw = new StreamWriter("CronServiceLog.txt", true);
                tw.WriteLine(DateTime.Now);
                tw.WriteLine(s);
                tw.WriteLine("");
                tw.WriteLine("----------");
                tw.Close();
            }
        }
        private class dUrl
        {
            internal string url;
            internal bool toStart;
            internal dUrl(string url, bool toStart)
            {
                this.url = url;
                this.toStart = toStart;

            }
        }
        private ArrayList CronUrl;
        private int frequency = 2;
        private bool hda_ToLog = true;
        private void CronConfig()
        {
            CronUrl = new ArrayList();
            TextReader tr = new StreamReader("CronServiceConfig.txt");
            string s = tr.ReadLine();
            if (!String.IsNullOrEmpty(s)) {
                string[] ss = s.Split(';');
                foreach (string sss in ss)
                {
                    string surl = sss.Trim();
                    if (surl.StartsWith("http", true, System.Globalization.CultureInfo.CurrentCulture))
                    {
                        CronUrl.Add(new dUrl(surl, true));
                    }
                }
            }
            s = tr.ReadLine();
            int d = Convert.ToInt32(s);
            if (d > 0 && d < 10) frequency = d;
            s = tr.ReadLine();
            if (!String.IsNullOrEmpty(s) && s.StartsWith("true", true, System.Globalization.CultureInfo.CurrentCulture))
            {
                hda_ToLog = true;
            }
            else hda_ToLog = false;

        }
        private static string StripHTML(string source)
        {
            try
            {
                string result;

                // Remove HTML Development formatting
                // Replace line breaks with space
                // because browsers inserts space
                result = source.Replace("\r", " ");
                // Replace line breaks with space
                // because browsers inserts space
                result = result.Replace("\n", " ");
                // Remove step-formatting
                result = result.Replace("\t", string.Empty);
                // Remove repeating spaces because browsers ignore them
                result = System.Text.RegularExpressions.Regex.Replace(result,
                                                                      @"( )+", " ");

                // Remove the header (prepare first by clearing attributes)
                result = System.Text.RegularExpressions.Regex.Replace(result,
                         @"<( )*head([^>])*>", "<head>",
                         System.Text.RegularExpressions.RegexOptions.IgnoreCase);
                result = System.Text.RegularExpressions.Regex.Replace(result,
                         @"(<( )*(/)( )*head( )*>)", "</head>",
                         System.Text.RegularExpressions.RegexOptions.IgnoreCase);
                result = System.Text.RegularExpressions.Regex.Replace(result,
                         "(<head>).*(</head>)", string.Empty,
                         System.Text.RegularExpressions.RegexOptions.IgnoreCase);

                // remove all scripts (prepare first by clearing attributes)
                result = System.Text.RegularExpressions.Regex.Replace(result,
                         @"<( )*script([^>])*>", "<script>",
                         System.Text.RegularExpressions.RegexOptions.IgnoreCase);
                result = System.Text.RegularExpressions.Regex.Replace(result,
                         @"(<( )*(/)( )*script( )*>)", "</script>",
                         System.Text.RegularExpressions.RegexOptions.IgnoreCase);
                //result = System.Text.RegularExpressions.Regex.Replace(result,
                //         @"(<script>)([^(<script>\.</script>)])*(</script>)",
                //         string.Empty,
                //         System.Text.RegularExpressions.RegexOptions.IgnoreCase);
                result = System.Text.RegularExpressions.Regex.Replace(result,
                         @"(<script>).*(</script>)", string.Empty,
                         System.Text.RegularExpressions.RegexOptions.IgnoreCase);

                // remove all styles (prepare first by clearing attributes)
                result = System.Text.RegularExpressions.Regex.Replace(result,
                         @"<( )*style([^>])*>", "<style>",
                         System.Text.RegularExpressions.RegexOptions.IgnoreCase);
                result = System.Text.RegularExpressions.Regex.Replace(result,
                         @"(<( )*(/)( )*style( )*>)", "</style>",
                         System.Text.RegularExpressions.RegexOptions.IgnoreCase);
                result = System.Text.RegularExpressions.Regex.Replace(result,
                         "(<style>).*(</style>)", string.Empty,
                         System.Text.RegularExpressions.RegexOptions.IgnoreCase);

                // insert tabs in spaces of <td> tags
                result = System.Text.RegularExpressions.Regex.Replace(result,
                         @"<( )*td([^>])*>", "\t",
                         System.Text.RegularExpressions.RegexOptions.IgnoreCase);

                // insert line breaks in places of <BR> and <LI> tags
                result = System.Text.RegularExpressions.Regex.Replace(result,
                         @"<( )*br( )*>", "\r",
                         System.Text.RegularExpressions.RegexOptions.IgnoreCase);
                result = System.Text.RegularExpressions.Regex.Replace(result,
                         @"<( )*li( )*>", "\r",
                         System.Text.RegularExpressions.RegexOptions.IgnoreCase);

                // insert line paragraphs (double line breaks) in place
                // if <P>, <DIV> and <TR> tags
                result = System.Text.RegularExpressions.Regex.Replace(result,
                         @"<( )*div([^>])*>", "\r\r",
                         System.Text.RegularExpressions.RegexOptions.IgnoreCase);
                result = System.Text.RegularExpressions.Regex.Replace(result,
                         @"<( )*tr([^>])*>", "\r\r",
                         System.Text.RegularExpressions.RegexOptions.IgnoreCase);
                result = System.Text.RegularExpressions.Regex.Replace(result,
                         @"<( )*p([^>])*>", "\r\r",
                         System.Text.RegularExpressions.RegexOptions.IgnoreCase);

                // Remove remaining tags like <a>, links, images,
                // comments etc - anything that's enclosed inside < >
                result = System.Text.RegularExpressions.Regex.Replace(result,
                         @"<[^>]*>", string.Empty,
                         System.Text.RegularExpressions.RegexOptions.IgnoreCase);

                // replace special characters:
                result = System.Text.RegularExpressions.Regex.Replace(result,
                         @" ", " ",
                         System.Text.RegularExpressions.RegexOptions.IgnoreCase);

                result = System.Text.RegularExpressions.Regex.Replace(result,
                         @"&bull;", " * ",
                         System.Text.RegularExpressions.RegexOptions.IgnoreCase);
                result = System.Text.RegularExpressions.Regex.Replace(result,
                         @"&lsaquo;", "<",
                         System.Text.RegularExpressions.RegexOptions.IgnoreCase);
                result = System.Text.RegularExpressions.Regex.Replace(result,
                         @"&rsaquo;", ">",
                         System.Text.RegularExpressions.RegexOptions.IgnoreCase);
                result = System.Text.RegularExpressions.Regex.Replace(result,
                         @"&trade;", "(tm)",
                         System.Text.RegularExpressions.RegexOptions.IgnoreCase);
                result = System.Text.RegularExpressions.Regex.Replace(result,
                         @"&frasl;", "/",
                         System.Text.RegularExpressions.RegexOptions.IgnoreCase);
                result = System.Text.RegularExpressions.Regex.Replace(result,
                         @"&lt;", "<",
                         System.Text.RegularExpressions.RegexOptions.IgnoreCase);
                result = System.Text.RegularExpressions.Regex.Replace(result,
                         @"&gt;", ">",
                         System.Text.RegularExpressions.RegexOptions.IgnoreCase);
                result = System.Text.RegularExpressions.Regex.Replace(result,
                         @"&copy;", "(c)",
                         System.Text.RegularExpressions.RegexOptions.IgnoreCase);
                result = System.Text.RegularExpressions.Regex.Replace(result,
                         @"&reg;", "(r)",
                         System.Text.RegularExpressions.RegexOptions.IgnoreCase);
                // Remove all others. More can be added, see
                // http://hotwired.lycos.com/webmonkey/reference/special_characters/
                result = System.Text.RegularExpressions.Regex.Replace(result,
                         @"&(.{2,6});", string.Empty,
                         System.Text.RegularExpressions.RegexOptions.IgnoreCase);

                // for testing
                //System.Text.RegularExpressions.Regex.Replace(result,
                //       this.txtRegex.Text,string.Empty,
                //       System.Text.RegularExpressions.RegexOptions.IgnoreCase);

                // make line breaking consistent
                result = result.Replace("\n", "\r");

                // Remove extra line breaks and tabs:
                // replace over 2 breaks with 2 and over 4 tabs with 4.
                // Prepare first to remove any whitespaces in between
                // the escaped characters and remove redundant tabs in between line breaks
                result = System.Text.RegularExpressions.Regex.Replace(result,
                         "(\r)( )+(\r)", "\r\r",
                         System.Text.RegularExpressions.RegexOptions.IgnoreCase);
                result = System.Text.RegularExpressions.Regex.Replace(result,
                         "(\t)( )+(\t)", "\t\t",
                         System.Text.RegularExpressions.RegexOptions.IgnoreCase);
                result = System.Text.RegularExpressions.Regex.Replace(result,
                         "(\t)( )+(\r)", "\t\r",
                         System.Text.RegularExpressions.RegexOptions.IgnoreCase);
                result = System.Text.RegularExpressions.Regex.Replace(result,
                         "(\r)( )+(\t)", "\r\t",
                         System.Text.RegularExpressions.RegexOptions.IgnoreCase);
                // Remove redundant tabs
                result = System.Text.RegularExpressions.Regex.Replace(result,
                         "(\r)(\t)+(\r)", "\r\r",
                         System.Text.RegularExpressions.RegexOptions.IgnoreCase);
                // Remove multiple tabs following a line break with just one tab
                result = System.Text.RegularExpressions.Regex.Replace(result,
                         "(\r)(\t)+", "\r\t",
                         System.Text.RegularExpressions.RegexOptions.IgnoreCase);
                // Initial replacement target string for line breaks
                string breaks = "\r\r\r";
                // Initial replacement target string for tabs
                string tabs = "\t\t\t\t\t";
                for (int index = 0; index < result.Length; index++)
                {
                    result = result.Replace(breaks, "\r\r");
                    result = result.Replace(tabs, "\t\t\t\t");
                    breaks = breaks + "\r";
                    tabs = tabs + "\t";
                }

                // That's it.
                return result;
            }
            catch (Exception e)
            {
                return "Fails to handle html return: " + e.Message + "\r\n" + source;
            }
        }
    }
}