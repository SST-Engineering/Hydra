using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace exec_tester
{
    class Program
    {
        static void Main(string[] args)
        {
            string p = "";
            Console.WriteLine("Saying this");
            Console.WriteLine("Now saying this");
            Console.Error.WriteLine("Saying to error strm");
            Console.Error.WriteLine("And this to err");
            Console.WriteLine("And this");
            Console.WriteLine("Get Input:");
            p = Console.ReadLine();
            Console.WriteLine("Say This:");
            p = Console.ReadLine();
            Console.WriteLine("Got " + p);
        /*    p += " " + Console.ReadLine();
            p += " " + Console.ReadLine();*/
            Environment.ExitCode = 66;
        }
    }
}
