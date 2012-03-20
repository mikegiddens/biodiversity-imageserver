#include "opencv2/imgproc/imgproc.hpp"
#include "opencv2/objdetect/objdetect.hpp"
#include "opencv2/highgui/highgui.hpp"

#include <iostream>
#include <string.h>
#include <ctype.h>
#include <cmath>

using namespace cv;
using namespace std;

int thresh = 50, N = 11;

double angle( Point pt1, Point pt2, Point pt0 )
{
    double dx1 = pt1.x - pt0.x;
    double dy1 = pt1.y - pt0.y;
    double dx2 = pt2.x - pt0.x;
    double dy2 = pt2.y - pt0.y;
    return (dx1*dx2 + dy1*dy2)/sqrt((dx1*dx1 + dy1*dy1)*(dx2*dx2 + dy2*dy2) + 1e-10);
}

void findSquares( const Mat& image, vector<Rect>& squares )
{   
    Mat gray0(image), gray;
    
	vector<vector<Point> > contours;

	Canny(gray0, gray, 0, thresh, 5);
	dilate(gray, gray, Mat(), Point(-1,-1));
	
	findContours(gray, contours, CV_RETR_LIST, CV_CHAIN_APPROX_SIMPLE);
	vector<Point> approx;
           
	for( size_t i = 0; i < contours.size(); i++ )
	{
		Rect rect = boundingRect(Mat(contours[i]));
		if (rect.width*rect.height > 300)
			squares.push_back(rect);
    }
}

void drawSquares( Mat& image, const vector<Rect>& squares )
{
    for( size_t i = 0; i < squares.size(); i++ )
    {
        rectangle(image, squares[i].tl(), squares[i].br(), Scalar(0,255,0), 2, 8, 0);
    }
}

void calcWidthHeight(const vector<Rect>&  squares)
{
	int mWidth = 0;
	int mHeight = 0;
	int x = -1;
	int y = -1;

	for( size_t i = 0; i < squares.size(); i++ )
	{
		if (mWidth < squares[i].width) mWidth = squares[i].width;
		if (mHeight < squares[i].height) mHeight = squares[i].height;
		x = squares[i].x;
		y = squares[i].y;
	}
	
	cout << "{\"success\":true, \"data\": {\"x\":" << x << ", \"y\":" << y << ", \"width\":" << mWidth << ", \"height\":" << mHeight << "}}" << endl;
}

int main(int argc, char** argv)
{
	Mat img, img2;
	vector<Rect> squares;
	string filename, color;
	int hmax, hmin, smin, vmin;
    
	if (argc > 1)
		filename = string(argv[1]);
	if (argc > 2)
	{
		color = string(argv[2]);
		if ((color == "red") || (color == "FF0000"))
		{
			hmax = 185; hmin = 175; smin = 215; vmin = 190;
		}
		if ((color == "blue") || (color == "0000FF"))
		{
			hmax = 125; hmin = 115; smin = 200; vmin = 80;
		}
		if ((color == "cyan") || (color == "00FFFF"))
		{
			hmax = 105; hmin = 95; smin = 200; vmin = 200;
		}
		if ((color == "green") || (color == "00FF00"))
		{
			hmax = 80; hmin = 70; smin = 220; vmin = 110;
		}
		if ((color == "yellow") || (color == "FFFF00"))
		{
			hmax = 30; hmin = 20; smin = 180; vmin = 220;
		}
		if ((color == "pink") || (color == "FF00FF"))
		{
			hmax = 170; hmin = 160; smin = 210; vmin = 195;
		}
	}
	else
	{
		hmax = 185; hmin = 175; smin = 90; vmin = 90;
	}

	img = imread(filename);
	
	cvtColor(img, img2, CV_BGR2HSV);
	
	Mat imgThreshed(img.rows, img.cols, CV_8UC1);

	for (int i=0; i<img.rows; i++)
		for (int j=0; j<img.cols; j++)
		{
			int h = img2.at<Vec3b>(i,j)[0];
			int s = img2.at<Vec3b>(i,j)[1];
			int v = img2.at<Vec3b>(i,j)[2];

			if ((h > hmin) && (h < hmax) && (s > smin) && (v > vmin))
				imgThreshed.at<uchar>(i, j) = 255;
			else
				imgThreshed.at<uchar>(i, j) = 0;
		}
	
	Mat kernel = Mat::eye(3, 3, CV_8UC1);
	imgThreshed.at<uchar>(1,1) = 0; imgThreshed.at<uchar>(0,1) = 0; imgThreshed.at<uchar>(2,1) = 0;

	dilate(imgThreshed, imgThreshed, kernel, Point(-1,-1), 2);
	findSquares(imgThreshed, squares);
	drawSquares(img, squares);
	calcWidthHeight(squares);

}