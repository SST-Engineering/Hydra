using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Text.RegularExpressions;
using Microsoft.AnalysisServices.AdomdClient;

namespace php_mdx
{
    class Program
    {
        private static RunMdx runMdx;
        static void Main(string[] args)
        {
            bool ok;
            if (args.Length < 2)
            {
                Console.Error.WriteLine("Wrong parameter count, php_mdx(connection_string, mdx_command)");
                Environment.Exit(1);
                return;
            }
            Console.Error.WriteLine("Connection String: " + args[0]);
            Console.Error.WriteLine("Mdx Command: " + args[1]);
            

            runMdx = new RunMdx(args[0], args[1]);
            ok = runMdx.run();
            if (!ok)
            {
                Console.Error.WriteLine("Fails to execute Mdx " + runMdx.last_error);
                Environment.Exit(2);
                return;
            }
            else
            {
                string dir = System.IO.Directory.GetCurrentDirectory();
                Console.Error.WriteLine("Current dir " + dir);
                System.IO.StreamWriter fstream = System.IO.File.CreateText(dir + "\\mdx_output.csv");
                runMdx.WriteToStream(fstream);
                fstream.Close();
                Environment.Exit(0);
            }
        }
    }
    internal class RunMdx
    {
        internal string mdxCommand;
        internal string connectionString;
        internal string last_error = null;
        internal RunMdx(string connectionString, string mdxCommand)
        {
            this.mdxCommand = mdxCommand;
            this.connectionString = connectionString;
        }
        internal Microsoft.AnalysisServices.AdomdClient.AdomdConnection NativeConnection = null;
        internal Microsoft.AnalysisServices.AdomdClient.AdomdCommand NativeCommand = null;
        internal Microsoft.AnalysisServices.AdomdClient.CellSet NativeCellSet = null;
        internal bool run()
        {
            Console.Error.WriteLine("Using connection " + this.connectionString);
            this.NativeConnection = new Microsoft.AnalysisServices.AdomdClient.AdomdConnection(this.connectionString);
            this.NativeConnection.Open();
            this.NativeCommand = new Microsoft.AnalysisServices.AdomdClient.AdomdCommand(mdxCommand, this.NativeConnection);
            this.NativeCellSet = this.NativeCommand.ExecuteCellSet();
            return true;
        }
        private System.IO.StreamWriter fstream;
        internal void WriteToStream(System.IO.StreamWriter fstream)
        {
            this.fstream = fstream;
            this.collectResults(this.NativeCellSet);
        }
        private void collectResults(Microsoft.AnalysisServices.AdomdClient.CellSet results) {
            AxisCollection axes = results.Axes;
            Axis axisColumns = axes[0]; // column
            StringBuilder s = new StringBuilder();
            s.Append("0|");
            for (int column = 0; column < axisColumns.Positions.Count; column++)
            {
                Position position = axisColumns.Positions[column];
                for (int atMember = 0; atMember < position.Members.Count; atMember++)
                {
                    s.Append(position.Members[atMember].UniqueName+"|");
                }
            }
            fstream.WriteLine(s.ToString().Trim(new char[]{'|'}));
            s.Clear();
            Axis axisRows = axes[1]; // rows
            int atCell = 0;
            for (int row = 0; row < axisRows.Positions.Count; row++)
            {
                Position position = axisRows.Positions[row];
                for (int atMember = 0; atMember < position.Members.Count; atMember++)
                {
                    s.Append(position.Members[atMember].UniqueName+"|");
                    for (int column = 0; column < axisColumns.Positions.Count; column++)
                    {
                        s.Append(results.Cells[atCell].FormattedValue+"|");
                        atCell++;
                    }
                    fstream.WriteLine(s.ToString().Trim(new char[] { '|' }));
                    s.Clear();
                }
            }


        }

    }
}
