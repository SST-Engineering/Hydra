using System;
using System.Collections.Generic;
using System.ComponentModel;
using System.Data;
using System.Drawing;
using System.Linq;
using System.Text;
using System.Windows.Forms;

using System.IO;
using System.Net;
using System.Web;
using System.Xml;

namespace ALCW_SoapTest
{
    public partial class Form1 : Form
    {
        public Form1()
        {
            InitializeComponent();
        }

        private StringBuilder errText = new StringBuilder();
        private void SayError(string err)
        {
            errText.Append(err);
            alcw_soap_error.Text = errText.ToString();
        }

        private void alcw_soap_go_Click(object sender, EventArgs e)
        {
            System.Net.HttpWebRequest oRequest = (System.Net.HttpWebRequest)System.Net.WebRequest.Create(alcw_url.Text);
            //     oRequest.KeepAlive = true;
            oRequest.AutomaticDecompression = DecompressionMethods.GZip; //  | DecompressionMethods.Deflate;
            oRequest.Method = "POST";
            oRequest.ContentType = "text/xml";
            oRequest.Accept = "text/xml";
            oRequest.UseDefaultCredentials = true;
            oRequest.Credentials = CredentialCache.DefaultNetworkCredentials;
            oRequest.SendChunked = false;
            System.Text.Encoding enc = System.Text.Encoding.ASCII;
            string _xml = alcw_soap_request.Text;
            byte[] oBytes = enc.GetBytes(_xml);
            System.IO.Stream oStream = null;
            try
            {
                oStream = oRequest.GetRequestStream();
            }
            catch (Exception ex)
            {
                SayError("Exception " + ex.Message + ": at Request.GetRequestStream - lost connection maybe");
            }
            oStream.Write(oBytes, 0, oBytes.Length);
            oStream.Flush();
            oStream.Close();
            System.Net.WebResponse oResponse = null;
            try
            {
                oResponse = oRequest.GetResponse();
            }
            catch (System.Net.WebException ex)
            {
                SayError("WebException " + ex.Message + " at oRequest.GetResponse");
                StringBuilder sb = new StringBuilder();
                sb.AppendLine(ex.Message);
                try
                {
                    StreamReader s = new StreamReader(ex.Response.GetResponseStream());
                    sb.Append(s.ReadToEnd());
                    sb.AppendLine("");
                }
                catch (Exception ee)
                {
                    SayError("Exception " + ee.Message + ": at Exception.Response.GetResponseStream - lost connection maybe");
                }
                string text = sb.ToString();
                SayError(text);
            }
            catch (Exception ex)
            {
                SayError("Exception " + ex.Message + ": at oResponse.GetResponse - lost connection maybe");
            }

            string sResponse = null;
            try
            {
                oStream = oResponse.GetResponseStream();
                StreamReader sr = new StreamReader(oStream, enc);
                sResponse = sr.ReadToEnd();
                alcw_soap_response.Text = sResponse;
                sr.Close();
            }
            catch (System.Net.WebException ex)
            {
                SayError("WebException " + ex.Message + " at oRequest.GetResponseStream and ReadToEnd");
                StringBuilder sb = new StringBuilder();
                sb.AppendLine(ex.Message);
                try
                {
                    StreamReader s = new StreamReader(ex.Response.GetResponseStream());
                    sb.Append(s.ReadToEnd());
                    sb.AppendLine("");
                }
                catch (Exception ee)
                {
                    SayError("Exception " + ee.Message + ": at Exception.Response.GetResponseStream - lost connection maybe");
                }
                string text = sb.ToString();
                throw new Exception(text);
            }
            catch (Exception ex)
            {
                SayError("Exception " + ex.Message + ": at oResponse.GetResponseStream - lost connection maybe");
            }

        }
    }
}
