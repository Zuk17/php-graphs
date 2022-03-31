#include <graphics.h>
#include <conio.h>
#include <stdio.h>
#define MAX 100
#define N 8
#define ZOOM 2

enum TypeSpline {Bezier,BSpline,Ermit};

inline double BezierFunc(double p[],int i,double t)
{
double s=1-t,
     t2=t*t,
     t3=t2*t;
return ((p[3*i]*s+3*t*p[3*i+1])*s+3*t2*p[3*i+2])*s+t3*p[3*i+3];
}

inline double BSplineFunc(double p[],int i,double t)
{
double s=1-t,
     t2=t*t,
     t3=t2*t;
return (s*s*s*p[i]+(3*t3-6*t2+4)*p[i+1]+(-3*t3+3*t2+3*t+1)*p[i+2]+t3*p[i+3])/6.0;
}

inline double ErmitFunc(double p[],int i,double t)
{
double t2=t*t,
     t3=t2*t;
return (2*t3-3*t2+1)*p[i]+(-2*t3+3*t2)*p[i+1]+(t3-2*t2+t)*(p[i+1]-p[i])+(t3-t2)*(p[i+2]-p[i+1]);
}

double (*Spline[])(double[],int,double)={BezierFunc,BSplineFunc,ErmitFunc};

inline int LoadFromFile(double a[],double b[],int &c)
{
 FILE *f;
 c=0;
 if((f=fopen("points.ptn","r"))==NULL) {
    puts("\nError: Can't open data file\n"); return -1;}
 while(!feof(f))
   {
    fscanf(f,"\%lf",&a[c]);
    fscanf(f,"\%lf",&b[c]);
    a[c]*=ZOOM;
    b[c++]*=ZOOM;
   }
 fclose(f);
 return 1;
}

inline void DrawFigure(double a[],double b[],int c,int xmid,int ymid,int col)
{
 setcolor(col);
 setlinestyle(1,1,2);
 moveto(a[0]+xmid,b[0]+ymid);
 for(int i=1;i<c;i++)
   lineto(a[i]+xmid,b[i]+ymid);
}

inline void DrawSpline(TypeSpline ts,double a[],double b[],int &c,int xmid,int ymid,int col)
{
 int i,j,end;
 double xs,ys,dt;
 char *s[]={   "                 Bezier Spline",
           "                  B-Spline    ",
           "                 Ermit Spline "};

 setcolor(col);
 setlinestyle(0,1,1);
 switch(ts) {
   case Bezier: end=c/3;break;
   case BSpline: end=c-3;break;
   case Ermit: end=c-2;break;}

 moveto(Spline[ts](a,0,0.0)+xmid,Spline[ts](b,0,0.0)+ymid);
 for(i=0;i<end;i++)
   for(j=0;j<=N;j++) {
     dt=(double)j/(double)N;
     xs=Spline[ts](a,i,dt);
     ys=Spline[ts](b,i,dt);
     lineto(xs+xmid,ys+ymid);
   }
 outtextxy(0,0,s[ts]);
}

main()
{
 TypeSpline type;
 int count,gdr=DETECT,gmod;
 initgraph(&gdr,&gmod,"");

 double x[MAX],y[MAX];
 if(!LoadFromFile(x,y,count)) return -1;

 for(type=Bezier;type<=Ermit;type++) {
   DrawFigure(x,y,count,320,240,14);
   DrawSpline(type,x,y,count,320,240,15);
   getch();
   cleardevice();}
}